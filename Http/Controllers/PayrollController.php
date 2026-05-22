<?php

// المسار الكامل: app/Http/Controllers/PayrollController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(protected PayrollService $payrollService) {}

    // ─── قائمة الرواتب ───────────────────────────────────────────────

    public function index(Request $request): View
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        $query = Payroll::with('employee.department')
            ->where('month', $month)
            ->where('year', $year)
            ->latest();

        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrolls    = $query->paginate(20)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();

        $totals = Payroll::where('month', $month)->where('year', $year)->selectRaw('
            SUM(gross_salary) as total_gross,
            SUM(total_deductions) as total_deductions,
            SUM(net_salary) as total_net,
            COUNT(*) as count
        ')->first();

        $alreadyGenerated = Payroll::where('month', $month)->where('year', $year)->exists();

        return view('payroll.index', compact(
            'payrolls', 'departments', 'totals',
            'month', 'year', 'alreadyGenerated'
        ));
    }

    // ─── نموذج توليد الرواتب ─────────────────────────────────────────

    public function generate(): View
    {
        $departments     = Department::active()->orderBy('name')->get();
        $activeEmployees = Employee::active()->with('salaryStructure', 'department')->get();

        return view('payroll.generate', compact('departments', 'activeEmployees'));
    }

    // ─── توليد رواتب الشهر ───────────────────────────────────────────

    public function processGenerate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month'        => 'required|integer|between:1,12',
            'year'         => 'required|integer|min:2020|max:2099',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $alreadyExists = Payroll::where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->whereIn('employee_id', $validated['employee_ids'])
            ->exists();

        if ($alreadyExists) {
            return back()->with('error', 'الرواتب لهذا الشهر مولَّدة مسبقاً لبعض الموظفين. يرجى حذفها أولاً.');
        }

        $count = $this->payrollService->generateForMonth(
            $validated['month'],
            $validated['year'],
            $validated['employee_ids']
        );

        ActivityLog::log('create', 'Payroll', null, null, [
            'month' => $validated['month'],
            'year'  => $validated['year'],
            'count' => $count,
        ]);

        return redirect()->route('payroll.index', [
            'month' => $validated['month'],
            'year'  => $validated['year'],
        ])->with('success', "تم توليد رواتب {$count} موظف بنجاح.");
    }

    // ─── عرض راتب موظف ───────────────────────────────────────────────

    public function show(Payroll $payroll): View
    {
        $payroll->load('employee.department');
        return view('payroll.show', compact('payroll'));
    }

    // ─── اعتماد راتب ─────────────────────────────────────────────────

    public function approve(Payroll $payroll): RedirectResponse
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', 'لا يمكن اعتماد هذا الراتب — حالته ليست مسودة.');
        }

        $payroll->update(['status' => 'approved']);

        ActivityLog::log('update', 'Payroll', $payroll->id, ['status' => 'draft'], ['status' => 'approved']);

        return back()->with('success', 'تم اعتماد الراتب بنجاح.');
    }

    // ─── اعتماد جميع رواتب الشهر ────────────────────────────────────

    public function approveAll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020',
        ]);

        $count = Payroll::where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->where('status', 'draft')
            ->update(['status' => 'approved']);

        ActivityLog::log('update', 'Payroll', null, null, [
            'action' => 'approve_all',
            'month'  => $validated['month'],
            'year'   => $validated['year'],
            'count'  => $count,
        ]);

        return back()->with('success', "تم اعتماد {$count} راتب بنجاح.");
    }

    // ─── تسجيل دفع الراتب ────────────────────────────────────────────

    public function markPaid(Request $request, Payroll $payroll): RedirectResponse
    {
        if ($payroll->status !== 'approved') {
            return back()->with('error', 'يجب اعتماد الراتب أولاً قبل تسجيل الدفع.');
        }

        $validated = $request->validate([
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string|max:100',
        ]);

        $this->payrollService->markPaid($payroll, $validated['payment_date'], $validated['payment_method']);

        ActivityLog::log('update', 'Payroll', $payroll->id, ['status' => 'approved'], ['status' => 'paid']);

        return back()->with('success', 'تم تسجيل دفع الراتب وإنشاء القيد المحاسبي.');
    }

    // ─── تعديل راتب يدوي ─────────────────────────────────────────────

    public function update(Request $request, Payroll $payroll): RedirectResponse
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', 'لا يمكن تعديل راتب معتمد أو مدفوع.');
        }

        $validated = $request->validate([
            'overtime_amount'  => 'nullable|numeric|min:0',
            'bonus'            => 'nullable|numeric|min:0',
            'absence_deduction'=> 'nullable|numeric|min:0',
            'late_deduction'   => 'nullable|numeric|min:0',
            'advance_deduction'=> 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        $this->payrollService->recalculate($payroll, $validated);

        return back()->with('success', 'تم تحديث الراتب وإعادة الحساب.');
    }

    // ─── حذف راتب ────────────────────────────────────────────────────

    public function destroy(Payroll $payroll): RedirectResponse
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'لا يمكن حذف راتب مدفوع.');
        }

        $month = $payroll->month;
        $year  = $payroll->year;

        ActivityLog::log('delete', 'Payroll', $payroll->id, $payroll->toArray(), null);
        $payroll->delete();

        return redirect()->route('payroll.index', compact('month', 'year'))
            ->with('success', 'تم حذف الراتب بنجاح.');
    }

    // ─── طباعة قسيمة الراتب PDF ──────────────────────────────────────

    public function payslip(Payroll $payroll)
    {
        $payroll->load('employee.department', 'createdBy');

        $pdf = Pdf::loadView('pdf.payslip', compact('payroll'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("payslip-{$payroll->employee->employee_number}-{$payroll->month}-{$payroll->year}.pdf");
    }

    // ─── تقرير الرواتب ───────────────────────────────────────────────

    public function report(Request $request): View
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        $payrolls = Payroll::with('employee.department')
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', '!=', 'draft')
            ->get();

        $byDepartment = $payrolls->groupBy(fn($p) => $p->employee->department?->name ?? 'بدون قسم')
            ->map(fn($group) => [
                'count'     => $group->count(),
                'gross'     => $group->sum('gross_salary'),
                'net'       => $group->sum('net_salary'),
                'deductions'=> $group->sum('total_deductions'),
            ]);

        return view('payroll.report', compact('payrolls', 'byDepartment', 'month', 'year'));
    }
}
