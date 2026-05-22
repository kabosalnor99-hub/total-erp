<?php

// المسار الكامل: app/Models/SalaryStructure.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStructure extends Model
{
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'housing_allowance',
        'transport_allowance',
        'food_allowance',
        'other_allowances',
        'social_insurance',
        'tax_deduction',
        'other_deductions',
        'is_active',
        'effective_from',
        'notes',
    ];

    protected $casts = [
        'basic_salary'       => 'decimal:2',
        'housing_allowance'  => 'decimal:2',
        'transport_allowance'=> 'decimal:2',
        'food_allowance'     => 'decimal:2',
        'other_allowances'   => 'decimal:2',
        'social_insurance'   => 'decimal:2',
        'tax_deduction'      => 'decimal:2',
        'other_deductions'   => 'decimal:2',
        'is_active'          => 'boolean',
        'effective_from'     => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTotalAllowancesAttribute(): float
    {
        return (float) (
            $this->housing_allowance +
            $this->transport_allowance +
            $this->food_allowance +
            $this->other_allowances
        );
    }

    public function getTotalDeductionsAttribute(): float
    {
        return (float) (
            $this->social_insurance +
            $this->tax_deduction +
            $this->other_deductions
        );
    }

    public function getGrossSalaryAttribute(): float
    {
        return (float) ($this->basic_salary + $this->total_allowances);
    }

    public function getNetSalaryAttribute(): float
    {
        return (float) ($this->gross_salary - $this->total_deductions);
    }
}
