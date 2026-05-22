<?php

// المسار الكامل: app/Services/PayrollService.php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    // ─── ثوابت أكواد الحسابات ─────────────────────────────────────────
    const ACCOUNT_SALARIES    = '5201'; // مصاريف الرواتب
    const ACCOUNT_CASH        = '1001'; // الصندوق النقدي
    const ACCOUNT_SALARY_PAY  = '2101'; // رواتب مستحقة الدفع

    // ─── توليد رواتب شهر كامل ────────────────────────────────────────

    public function generateForMonth(int $month, int $year, array $employeeIds): int
    {
        $count = 0;

        DB::transaction(function () use ($month, $year, $employeeIds, &$count) {
            $employees = Employee::with('salaryStructure')
                ->whereIn('id', $employeeIds)
                ->where('status', '!=', 'terminated')
                ->get();

            foreach ($employees as $employee) {
                $this->generateSingle($employee, $month, $year);
                $count++;
            }
        });

        return $count;
    }

    // ─── توليد راتب موظف واحد ────────────────────────────────────────

    public function generateSingle(Employee $employee, int $month, int $year): Payroll
    {
        $structure = $employee->salaryStructure;

        // بيانات الحضور
        $attendance = Attendance::forEmployee($employee->id)
            ->forMonth($month, $year)
            ->get();

        $workingDays   = $attendance->whereIn('status', ['present', 'late'])->count();
        $absentDays    = $attendance->where('status', 'absent')->count();
        $lateMinutes   = (int) $attendance->sum('late_minutes');
        $overtimeHours = (int) ($attendance->sum('overtime_minutes') / 60);

        // الراتب الأساسي
        $basicSalary       = $structure ? (float) $structure->basic_salary       : (float) $employee->basic_salary;
        $housingAllowance  = $structure ? (float) $structure->housing_allowance  : 0;
        $transportAllowance= $structure ? (float) $structure->transport_allowance: 0;
        $foodAllowance     = $structure ? (float) $structure->food_allowance     : 0;
        $otherAllowances   = $structure ? (float) $structure->other_allowances   : 0;
        $socialInsurance   = $structure ? (float) $structure->social_insurance   : 0;
        $taxDeduction      = $structure ? (float) $structure->tax_deduction      : 0;
        $otherDeductions   = $structure ? (float) $structure->other_deductions   : 0;

        // حساب خصم الغياب (يوم = راتب أساسي ÷ 30)
        $dailyRate       = $basicSalary / 30;
        $absenceDeduction = round($dailyRate * $absentDays, 2);

        // حساب خصم التأخير (دقيقة = راتب أساسي ÷ 30 ÷ 8 ÷ 60)
        $minuteRate    = $basicSalary / 30 / 8 / 60;
        $lateDeduction = round($minuteRate * $lateMinutes, 2);

        // حساب بدل الأوفرتايم (ساعة = راتب أساسي ÷ 30 ÷ 8 × 1.5)
        $hourlyRate      = $basicSalary / 30 / 8;
        $overtimeAmount  = round($hourlyRate * 1.5 * $overtimeHours, 2);

        // الإجماليات
        $grossSalary    = $basicSalary + $housingAllowance + $transportAllowance
                        + $foodAllowance + $otherAllowances + $overtimeAmount;

        $totalDeductions = $absenceDeduction + $lateDeduction + $socialInsurance
                         + $taxDeduction + $otherDeductions;

        $netSalary = max(0, $grossSalary - $totalDeductions);

        return Payroll::create([
            'employee_id'        => $employee->id,
            'created_by'         => auth()->id() ?? 1,
            'month'              => $month,
            'year'               => $year,
            'basic_salary'       => $basicSalary,
            'housing_allowance'  => $housingAllowance,
            'transport_allowance'=> $transportAllowance,
            'food_allowance'     => $foodAllowance,
            'other_allowances'   => $otherAllowances,
            'overtime_amount'    => $overtimeAmount,
            'bonus'              => 0,
            'absence_deduction'  => $absenceDeduction,
            'late_deduction'     => $lateDeduction,
            'social_insurance'   => $socialInsurance,
            'tax_deduction'      => $taxDeduction,
            'other_deductions'   => $otherDeductions,
            'advance_deduction'  => 0,
            'gross_salary'       => $grossSalary,
            'total_deductions'   => $totalDeductions,
            'net_salary'         => $netSalary,
            'working_days'       => $workingDays,
            'absent_days'        => $absentDays,
            'late_minutes'       => $lateMinutes,
            'overtime_hours'     => $overtimeHours,
            'status'             => 'draft',
        ]);
    }

    // ─── إعادة حساب راتب بعد تعديل يدوي ─────────────────────────────

    public function recalculate(Payroll $payroll, array $overrides): Payroll
    {
        DB::transaction(function () use ($payroll, $overrides) {
            $payroll->fill($overrides);

            $grossSalary = $payroll->basic_salary
                + $payroll->housing_allowance
                + $payroll->transport_allowance
                + $payroll->food_allowance
                + $payroll->other_allowances
                + ($overrides['overtime_amount'] ?? $payroll->overtime_amount)
                + ($overrides['bonus'] ?? $payroll->bonus);

            $totalDeductions = ($overrides['absence_deduction'] ?? $payroll->absence_deduction)
                + ($overrides['late_deduction'] ?? $payroll->late_deduction)
                + $payroll->social_insurance
                + $payroll->tax_deduction
                + ($overrides['other_deductions'] ?? $payroll->other_deductions)
                + ($overrides['advance_deduction'] ?? $payroll->advance_deduction);

            $payroll->gross_salary    = $grossSalary;
            $payroll->total_deductions = $totalDeductions;
            $payroll->net_salary      = max(0, $grossSalary - $totalDeductions);

            $payroll->save();
        });

        return $payroll->fresh();
    }

    // ─── تسجيل دفع الراتب + قيد محاسبي ──────────────────────────────

    public function markPaid(Payroll $payroll, string $paymentDate, string $paymentMethod): void
    {
        DB::transaction(function () use ($payroll, $paymentDate, $paymentMethod) {
            $payroll->update([
                'status'         => 'paid',
                'payment_date'   => $paymentDate,
                'payment_method' => $paymentMethod,
            ]);

            // إنشاء القيد المحاسبي
            $this->createPayrollEntry($payroll);
        });
    }

    // ─── القيد المحاسبي للراتب ───────────────────────────────────────
    // من حـ/مصاريف الرواتب (مدين) → إلى حـ/الصندوق أو البنك (دائن)

    private function createPayrollEntry(Payroll $payroll): JournalEntry
    {
        $salariesAccount = $this->findAccount(self::ACCOUNT_SALARIES);
        $cashAccount     = $this->findAccount(self::ACCOUNT_CASH);

        $monthNames = [
            1=>'يناير', 2=>'فبراير', 3=>'مارس', 4=>'أبريل',
            5=>'مايو', 6=>'يونيو', 7=>'يوليو', 8=>'أغسطس',
            9=>'سبتمبر', 10=>'أكتوبر', 11=>'نوفمبر', 12=>'ديسمبر',
        ];

        $monthName = ($monthNames[$payroll->month] ?? '') . ' ' . $payroll->year;

        $entry = JournalEntry::create([
            'entry_number'   => JournalEntry::generateNumber(),
            'date'           => $payroll->payment_date,
            'description'    => "راتب {$payroll->employee->name} — {$monthName}",
            'user_id'        => auth()->id() ?? $payroll->created_by,
            'status'         => 'posted',
            'reference_type' => Payroll::class,
            'reference_id'   => $payroll->id,
        ]);

        // مدين: مصاريف الرواتب
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $salariesAccount?->id,
            'debit'            => $payroll->net_salary,
            'credit'           => 0,
            'description'      => "راتب {$payroll->employee->name} — {$monthName}",
        ]);

        // دائن: الصندوق / البنك
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $cashAccount?->id,
            'debit'            => 0,
            'credit'           => $payroll->net_salary,
            'description'      => "صرف راتب {$payroll->employee->name} — {$monthName}",
        ]);

        return $entry;
    }

    // ─── حساب مكافأة نهاية الخدمة ────────────────────────────────────

    public function calculateGratuity(Employee $employee): array
    {
        $years   = $employee->hire_date->diffInYears(now());
        $months  = $employee->hire_date->diffInMonths(now()) % 12;
        $monthly = (float) $employee->basic_salary;

        $amount = 0;
        if ($years >= 1) {
            if ($years <= 5) {
                $amount = $monthly * $years * 0.5;
            } else {
                $amount = ($monthly * 5 * 0.5) + ($monthly * ($years - 5));
            }
        }

        return [
            'years'   => $years,
            'months'  => $months,
            'amount'  => round($amount, 2),
            'monthly' => $monthly,
        ];
    }

    // ─── بحث عن حساب بالكود ──────────────────────────────────────────

    private function findAccount(string $code): ?Account
    {
        return Account::where('code', $code)->first();
    }
}
