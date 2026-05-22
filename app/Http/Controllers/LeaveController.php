<?php

// المسار الكامل: app/Http/Controllers/LeaveController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    // ─── قائمة الإجازات ──────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Leave::with('employee.department', 'approvedBy')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        $leaves    = $query->paginate(20)->withQueryString();
        $employees = Employee::active()->orderBy('name')->get();

        $stats = [
            'pending'  => Leave::where('status', 'pending')->count(),
            'approved' => Leave::where('status', 'approved')->whereYear('start_date', now()->year)->count(),
            'rejected' => Leave::where('status', 'rejected')->whereYear('start_date', now()->year)->count(),
        ];

        return view('leaves.index', compact('leaves', 'employees', 'stats'));
    }

    // ─── نموذج طلب إجازة جديدة ───────────────────────────────────────

    public function create(): View
    {
        $employees = Employee::active()->orderBy('name')->get();
        return view('leaves.create', compact('employees'));
    }

    // ─── حفظ طلب الإجازة ────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:annual,sick,emergency,unpaid,other',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string|max:1000',
        ]);

        // حساب عدد الأيام
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end   = \Carbon\Carbon::parse($validated['end_date']);
        $validated['days'] = $start->diffInDays($end) + 1;
        $validated['status'] = 'pending';

        // التحقق من رصيد الإجازات
        $employee = Employee::find($validated['employee_id']);

        if ($validated['type'] === 'annual' && $employee->annual_leave_balance < $validated['days']) {
            return back()->withInput()->with('error',
                "رصيد الإجازة السنوية غير كافٍ. المتاح: {$employee->annual_leave_balance} يوم."
            );
        }

        if ($validated['type'] === 'sick' && $employee->sick_leave_balance < $validated['days']) {
            return back()->withInput()->with('error',
                "رصيد الإجازة المرضية غير كافٍ. المتاح: {$employee->sick_leave_balance} يوم."
            );
        }

        // التحقق من تداخل الإجازات
        $overlap = Leave::where('employee_id', $validated['employee_id'])
            ->where('status', 'approved')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']]);
            })->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'يوجد تداخل مع إجازة معتمدة سابقة لهذا الموظف.');
        }

        $leave = Leave::create($validated);

        ActivityLog::log('create', 'Leave', $leave->id, null, $leave->toArray());

        return redirect()->route('leaves.index')
            ->with('success', 'تم تقديم طلب الإجازة بنجاح وهو في انتظار الاعتماد.');
    }

    // ─── عرض إجازة ───────────────────────────────────────────────────

    public function show(Leave $leave): View
    {
        $leave->load('employee.department', 'approvedBy');
        return view('leaves.show', compact('leave'));
    }

    // ─── اعتماد إجازة ────────────────────────────────────────────────

    public function approve(Leave $leave): RedirectResponse
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'هذا الطلب تمت معالجته مسبقاً.');
        }

        $employee = $leave->employee;

        // خصم من الرصيد
        if ($leave->type === 'annual') {
            $employee->decrement('annual_leave_balance', $leave->days);
        } elseif ($leave->type === 'sick') {
            $employee->decrement('sick_leave_balance', $leave->days);
        }

        // تغيير حالة الموظف
        if (now()->between($leave->start_date, $leave->end_date)) {
            $employee->update(['status' => 'on_leave']);
        }

        $leave->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()->toDateString(),
        ]);

        ActivityLog::log('update', 'Leave', $leave->id, ['status' => 'pending'], ['status' => 'approved']);

        return back()->with('success', 'تم اعتماد الإجازة وخصم الرصيد.');
    }

    // ─── رفض إجازة ───────────────────────────────────────────────────

    public function reject(Request $request, Leave $leave): RedirectResponse
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'هذا الطلب تمت معالجته مسبقاً.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leave->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        ActivityLog::log('update', 'Leave', $leave->id, ['status' => 'pending'], ['status' => 'rejected']);

        return back()->with('success', 'تم رفض طلب الإجازة.');
    }

    // ─── حذف طلب إجازة ───────────────────────────────────────────────

    public function destroy(Leave $leave): RedirectResponse
    {
        if ($leave->status === 'approved') {
            // إعادة الرصيد عند حذف إجازة معتمدة
            if ($leave->type === 'annual') {
                $leave->employee->increment('annual_leave_balance', $leave->days);
            } elseif ($leave->type === 'sick') {
                $leave->employee->increment('sick_leave_balance', $leave->days);
            }
        }

        ActivityLog::log('delete', 'Leave', $leave->id, $leave->toArray(), null);
        $leave->delete();

        return redirect()->route('leaves.index')
            ->with('success', 'تم حذف طلب الإجازة.');
    }
}
