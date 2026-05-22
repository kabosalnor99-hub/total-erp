<?php

// المسار الكامل: app/Http/Controllers/AttendanceController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    // ─── سجل الحضور ──────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $date  = $request->input('date', now()->toDateString());
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);
        $view  = $request->input('view', 'daily'); // daily | monthly

        if ($view === 'daily') {
            $query = Attendance::with('employee.department')
                ->whereDate('date', $date)
                ->latest();

            if ($request->filled('department_id')) {
                $query->whereHas('employee', fn($q) =>
                    $q->where('department_id', $request->department_id)
                );
            }

            $records = $query->paginate(25)->withQueryString();

            $stats = [
                'present'  => Attendance::whereDate('date', $date)->where('status', 'present')->count(),
                'absent'   => Attendance::whereDate('date', $date)->where('status', 'absent')->count(),
                'late'     => Attendance::whereDate('date', $date)->where('status', 'late')->count(),
                'on_leave' => Attendance::whereDate('date', $date)->where('status', 'on_leave')->count(),
                'total'    => Employee::active()->count(),
            ];

            $departments = Department::active()->orderBy('name')->get();

            return view('attendance.index', compact('records', 'stats', 'date', 'departments', 'view'));
        }

        // عرض شهري
        $employees = Employee::active()->with(['attendances' => function ($q) use ($month, $year) {
            $q->forMonth($month, $year);
        }])->get();

        return view('attendance.monthly', compact('employees', 'month', 'year', 'view'));
    }

    // ─── نموذج تسجيل الحضور ──────────────────────────────────────────

    public function create(): View
    {
        $employees = Employee::active()->orderBy('name')->get();
        $today     = now()->toDateString();

        // الموظفون الذين لم يُسجَّل حضورهم اليوم
        $recorded = Attendance::whereDate('date', $today)->pluck('employee_id')->toArray();
        $pending  = $employees->whereNotIn('id', $recorded)->values();

        return view('attendance.create', compact('employees', 'pending', 'today'));
    }

    // ─── حفظ سجلات الحضور ────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date'         => 'required|date',
            'records'      => 'required|array',
            'records.*.employee_id'       => 'required|exists:employees,id',
            'records.*.status'            => 'required|in:present,absent,late,on_leave,holiday',
            'records.*.check_in'          => 'nullable|date_format:H:i',
            'records.*.check_out'         => 'nullable|date_format:H:i',
            'records.*.late_minutes'      => 'nullable|integer|min:0',
            'records.*.overtime_minutes'  => 'nullable|integer|min:0',
            'records.*.notes'             => 'nullable|string|max:255',
        ]);

        $saved = 0;
        foreach ($validated['records'] as $rec) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $rec['employee_id'],
                    'date'        => $validated['date'],
                ],
                [
                    'status'           => $rec['status'],
                    'check_in'         => $rec['check_in']         ?? null,
                    'check_out'        => $rec['check_out']        ?? null,
                    'late_minutes'     => $rec['late_minutes']     ?? 0,
                    'overtime_minutes' => $rec['overtime_minutes'] ?? 0,
                    'notes'            => $rec['notes']            ?? null,
                ]
            );
            $saved++;
        }

        ActivityLog::log('create', 'Attendance', null, null, [
            'date'  => $validated['date'],
            'count' => $saved,
        ]);

        return redirect()->route('attendance.index', ['date' => $validated['date']])
            ->with('success', "تم تسجيل حضور {$saved} موظف بنجاح.");
    }

    // ─── تعديل سجل حضور ──────────────────────────────────────────────

    public function edit(Attendance $attendance): View
    {
        $attendance->load('employee');
        return view('attendance.edit', compact('attendance'));
    }

    // ─── حفظ تعديل سجل الحضور ────────────────────────────────────────

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $validated = $request->validate([
            'status'           => 'required|in:present,absent,late,on_leave,holiday',
            'check_in'         => 'nullable|date_format:H:i',
            'check_out'        => 'nullable|date_format:H:i',
            'late_minutes'     => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            'notes'            => 'nullable|string|max:255',
        ]);

        $old = $attendance->toArray();
        $attendance->update($validated);

        ActivityLog::log('update', 'Attendance', $attendance->id, $old, $attendance->fresh()->toArray());

        return redirect()->route('attendance.index', ['date' => $attendance->date->toDateString()])
            ->with('success', 'تم تحديث سجل الحضور.');
    }

    // ─── تسجيل حضور سريع (موظف واحد) ────────────────────────────────

    public function quickStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'date'             => 'required|date',
            'status'           => 'required|in:present,absent,late,on_leave,holiday',
            'check_in'         => 'nullable|date_format:H:i',
            'check_out'        => 'nullable|date_format:H:i',
            'late_minutes'     => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            'notes'            => 'nullable|string|max:255',
        ]);

        Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );

        return back()->with('success', 'تم تسجيل الحضور.');
    }

    // ─── تقرير الحضور الشهري لموظف ───────────────────────────────────

    public function employeeReport(Employee $employee, Request $request): View
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        $records = Attendance::forEmployee($employee->id)
            ->forMonth($month, $year)
            ->orderBy('date')
            ->get();

        $stats = [
            'present'       => $records->where('status', 'present')->count(),
            'absent'        => $records->where('status', 'absent')->count(),
            'late'          => $records->where('status', 'late')->count(),
            'on_leave'      => $records->where('status', 'on_leave')->count(),
            'total_late_min'=> $records->sum('late_minutes'),
            'total_ot_min'  => $records->sum('overtime_minutes'),
        ];

        return view('attendance.employee-report', compact('employee', 'records', 'stats', 'month', 'year'));
    }
}
