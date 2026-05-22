<?php

// المسار الكامل: app/Http/Controllers/EmployeeController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\SalaryStructure;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    // ─── قائمة الموظفين ──────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Employee::with('department')->latest();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees   = $query->paginate(15)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();

        $stats = [
            'total'      => Employee::count(),
            'active'     => Employee::where('status', 'active')->count(),
            'on_leave'   => Employee::where('status', 'on_leave')->count(),
            'terminated' => Employee::where('status', 'terminated')->count(),
        ];

        return view('employees.index', compact('employees', 'departments', 'stats'));
    }

    // ─── نموذج إنشاء موظف جديد ───────────────────────────────────────

    public function create(): View
    {
        $departments = Department::active()->orderBy('name')->get();
        $users       = User::orderBy('name')->get();
        $nextNumber  = Employee::generateNumber();

        return view('employees.create', compact('departments', 'users', 'nextNumber'));
    }

    // ─── حفظ موظف جديد ───────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_number'   => 'required|string|unique:employees,employee_number',
            'name'              => 'required|string|max:255',
            'name_en'           => 'nullable|string|max:255',
            'national_id'       => 'nullable|string|unique:employees,national_id',
            'nationality'       => 'nullable|string|max:100',
            'date_of_birth'     => 'nullable|date',
            'gender'            => 'required|in:male,female',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'address'           => 'nullable|string',
            'photo'             => 'nullable|image|max:2048',
            'department_id'     => 'nullable|exists:departments,id',
            'user_id'           => 'nullable|exists:users,id',
            'job_title'         => 'required|string|max:255',
            'contract_type'     => 'required|in:permanent,temporary,part_time,contract',
            'hire_date'         => 'required|date',
            'contract_end_date' => 'nullable|date|after:hire_date',
            'basic_salary'      => 'required|numeric|min:0',
            'status'            => 'required|in:active,on_leave,terminated',
            'bank_name'         => 'nullable|string|max:255',
            'bank_account'      => 'nullable|string|max:100',
            'annual_leave_balance' => 'nullable|integer|min:0',
            'sick_leave_balance'   => 'nullable|integer|min:0',
            'notes'             => 'nullable|string',
            // هيكل الراتب
            'housing_allowance'   => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'food_allowance'      => 'nullable|numeric|min:0',
            'other_allowances'    => 'nullable|numeric|min:0',
            'social_insurance'    => 'nullable|numeric|min:0',
            'tax_deduction'       => 'nullable|numeric|min:0',
            'other_deductions'    => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // رفع الصورة
            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('employees', 'public');
            }

            $employee = Employee::create($validated);

            // إنشاء هيكل الراتب إذا وُجدت بدلات
            if ($request->filled('housing_allowance') || $request->filled('transport_allowance') ||
                $request->filled('food_allowance') || $request->filled('other_allowances') ||
                $request->filled('social_insurance')) {

                SalaryStructure::create([
                    'employee_id'        => $employee->id,
                    'basic_salary'       => $validated['basic_salary'],
                    'housing_allowance'  => $request->housing_allowance   ?? 0,
                    'transport_allowance'=> $request->transport_allowance ?? 0,
                    'food_allowance'     => $request->food_allowance      ?? 0,
                    'other_allowances'   => $request->other_allowances    ?? 0,
                    'social_insurance'   => $request->social_insurance    ?? 0,
                    'tax_deduction'      => $request->tax_deduction       ?? 0,
                    'other_deductions'   => $request->other_deductions    ?? 0,
                    'is_active'          => true,
                    'effective_from'     => $validated['hire_date'],
                ]);
            }

            ActivityLog::log('create', 'Employee', $employee->id, null, $employee->toArray());
        });

        return redirect()->route('employees.index')
            ->with('success', 'تم إضافة الموظف بنجاح.');
    }

    // ─── عرض موظف ────────────────────────────────────────────────────

    public function show(Employee $employee): View
    {
        $employee->load(['department', 'user', 'salaryStructure', 'leaves', 'payrolls' => function ($q) {
            $q->latest()->limit(6);
        }]);

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        $attendance = Attendance::forEmployee($employee->id)
            ->forMonth($currentMonth, $currentYear)
            ->get();

        $attendanceStats = [
            'present'  => $attendance->where('status', 'present')->count(),
            'absent'   => $attendance->where('status', 'absent')->count(),
            'late'     => $attendance->where('status', 'late')->count(),
            'on_leave' => $attendance->where('status', 'on_leave')->count(),
        ];

        $pendingLeaves = $employee->leaves()->where('status', 'pending')->count();
        $gratuity      = $employee->gratuityAmount();

        return view('employees.show', compact(
            'employee', 'attendance', 'attendanceStats', 'pendingLeaves', 'gratuity'
        ));
    }

    // ─── نموذج التعديل ───────────────────────────────────────────────

    public function edit(Employee $employee): View
    {
        $employee->load('salaryStructure');
        $departments = Department::active()->orderBy('name')->get();
        $users       = User::orderBy('name')->get();

        return view('employees.edit', compact('employee', 'departments', 'users'));
    }

    // ─── حفظ التعديلات ───────────────────────────────────────────────

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'name_en'           => 'nullable|string|max:255',
            'national_id'       => 'nullable|string|unique:employees,national_id,' . $employee->id,
            'nationality'       => 'nullable|string|max:100',
            'date_of_birth'     => 'nullable|date',
            'gender'            => 'required|in:male,female',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'address'           => 'nullable|string',
            'photo'             => 'nullable|image|max:2048',
            'department_id'     => 'nullable|exists:departments,id',
            'user_id'           => 'nullable|exists:users,id',
            'job_title'         => 'required|string|max:255',
            'contract_type'     => 'required|in:permanent,temporary,part_time,contract',
            'hire_date'         => 'required|date',
            'contract_end_date' => 'nullable|date',
            'basic_salary'      => 'required|numeric|min:0',
            'status'            => 'required|in:active,on_leave,terminated',
            'bank_name'         => 'nullable|string|max:255',
            'bank_account'      => 'nullable|string|max:100',
            'annual_leave_balance' => 'nullable|integer|min:0',
            'sick_leave_balance'   => 'nullable|integer|min:0',
            'notes'             => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request, $employee) {
            $old = $employee->toArray();

            if ($request->hasFile('photo')) {
                if ($employee->photo) {
                    Storage::disk('public')->delete($employee->photo);
                }
                $validated['photo'] = $request->file('photo')->store('employees', 'public');
            }

            $employee->update($validated);

            ActivityLog::log('update', 'Employee', $employee->id, $old, $employee->fresh()->toArray());
        });

        return redirect()->route('employees.show', $employee)
            ->with('success', 'تم تحديث بيانات الموظف بنجاح.');
    }

    // ─── حذف موظف ────────────────────────────────────────────────────

    public function destroy(Employee $employee): RedirectResponse
    {
        if ($employee->payrolls()->exists()) {
            return back()->with('error', 'لا يمكن حذف موظف لديه سجلات رواتب.');
        }

        $old = $employee->toArray();

        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        $employee->delete();

        ActivityLog::log('delete', 'Employee', $employee->id, $old, null);

        return redirect()->route('employees.index')
            ->with('success', 'تم حذف الموظف بنجاح.');
    }

    // ─── تحديث هيكل الراتب ───────────────────────────────────────────

    public function updateSalary(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'basic_salary'       => 'required|numeric|min:0',
            'housing_allowance'  => 'nullable|numeric|min:0',
            'transport_allowance'=> 'nullable|numeric|min:0',
            'food_allowance'     => 'nullable|numeric|min:0',
            'other_allowances'   => 'nullable|numeric|min:0',
            'social_insurance'   => 'nullable|numeric|min:0',
            'tax_deduction'      => 'nullable|numeric|min:0',
            'other_deductions'   => 'nullable|numeric|min:0',
            'effective_from'     => 'required|date',
            'notes'              => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $employee) {
            // إلغاء تفعيل الهيكل القديم
            $employee->salaryStructures()->update(['is_active' => false]);

            // إنشاء هيكل جديد
            SalaryStructure::create([
                'employee_id'        => $employee->id,
                'basic_salary'       => $validated['basic_salary'],
                'housing_allowance'  => $validated['housing_allowance']   ?? 0,
                'transport_allowance'=> $validated['transport_allowance'] ?? 0,
                'food_allowance'     => $validated['food_allowance']      ?? 0,
                'other_allowances'   => $validated['other_allowances']    ?? 0,
                'social_insurance'   => $validated['social_insurance']    ?? 0,
                'tax_deduction'      => $validated['tax_deduction']       ?? 0,
                'other_deductions'   => $validated['other_deductions']    ?? 0,
                'is_active'          => true,
                'effective_from'     => $validated['effective_from'],
                'notes'              => $validated['notes'] ?? null,
            ]);

            // تحديث الراتب الأساسي في بيانات الموظف
            $employee->update(['basic_salary' => $validated['basic_salary']]);

            ActivityLog::log('update', 'SalaryStructure', $employee->id, null, $validated);
        });

        return back()->with('success', 'تم تحديث هيكل الراتب بنجاح.');
    }
}
