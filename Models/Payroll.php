<?php

// المسار الكامل: app/Models/Payroll.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id', 'created_by', 'month', 'year',
        'basic_salary', 'housing_allowance', 'transport_allowance',
        'food_allowance', 'other_allowances', 'overtime_amount', 'bonus',
        'absence_deduction', 'late_deduction', 'social_insurance',
        'tax_deduction', 'other_deductions', 'advance_deduction',
        'gross_salary', 'total_deductions', 'net_salary',
        'working_days', 'absent_days', 'late_minutes', 'overtime_hours',
        'status', 'payment_date', 'payment_method', 'notes',
    ];

    protected $casts = [
        'basic_salary'       => 'decimal:2',
        'housing_allowance'  => 'decimal:2',
        'transport_allowance'=> 'decimal:2',
        'food_allowance'     => 'decimal:2',
        'other_allowances'   => 'decimal:2',
        'overtime_amount'    => 'decimal:2',
        'bonus'              => 'decimal:2',
        'absence_deduction'  => 'decimal:2',
        'late_deduction'     => 'decimal:2',
        'social_insurance'   => 'decimal:2',
        'tax_deduction'      => 'decimal:2',
        'other_deductions'   => 'decimal:2',
        'advance_deduction'  => 'decimal:2',
        'gross_salary'       => 'decimal:2',
        'total_deductions'   => 'decimal:2',
        'net_salary'         => 'decimal:2',
        'payment_date'       => 'date',
    ];

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        // nullable — المستخدم قد يُحذف لاحقاً دون تأثير على الرواتب
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getMonthNameAttribute(): string
    {
        $months = [
            1=>'يناير', 2=>'فبراير', 3=>'مارس', 4=>'أبريل',
            5=>'مايو',  6=>'يونيو',  7=>'يوليو', 8=>'أغسطس',
            9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر',
        ];
        return ($months[$this->month] ?? '') . ' ' . $this->year;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'    => 'مسودة',
            'approved' => 'معتمد',
            'paid'     => 'مدفوع',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'    => 'bg-gray-100 text-gray-600',
            'approved' => 'bg-blue-100 text-blue-700',
            'paid'     => 'bg-green-100 text-green-700',
            default    => 'bg-gray-100 text-gray-600',
        };
    }
}
