<?php

// المسار الكامل: app/Models/Employee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = [
        'employee_number',
        'name',
        'name_en',
        'national_id',
        'nationality',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'address',
        'photo',
        'department_id',
        'user_id',
        'job_title',
        'contract_type',
        'hire_date',
        'contract_end_date',
        'basic_salary',
        'status',
        'bank_name',
        'bank_account',
        'annual_leave_balance',
        'sick_leave_balance',
        'notes',
    ];

    protected $casts = [
        'date_of_birth'     => 'date',
        'hire_date'         => 'date',
        'contract_end_date' => 'date',
        'basic_salary'      => 'decimal:2',
    ];

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salaryStructure(): HasOne
    {
        return $this->hasOne(SalaryStructure::class)->where('is_active', true)->latestOfMany('effective_from');
    }

    public function salaryStructures(): HasMany
    {
        return $this->hasMany(SalaryStructure::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('employee_number', 'like', "%{$term}%")
              ->orWhere('job_title', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : asset('images/default-avatar.png');
    }

    public function getYearsOfServiceAttribute(): float
    {
        return round($this->hire_date->diffInMonths(now()) / 12, 1);
    }

    public function getContractTypeLabelAttribute(): string
    {
        return match ($this->contract_type) {
            'permanent'  => 'دائم',
            'temporary'  => 'مؤقت',
            'part_time'  => 'جزء من الوقت',
            'contract'   => 'عقد',
            default      => $this->contract_type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'     => 'نشط',
            'on_leave'   => 'في إجازة',
            'terminated' => 'منتهية خدمته',
            default      => $this->status,
        };
    }

    public function getGrossNetSalaryAttribute(): float
    {
        $structure = $this->salaryStructure;
        if (!$structure) return (float) $this->basic_salary;

        return (float) (
            $structure->basic_salary +
            $structure->housing_allowance +
            $structure->transport_allowance +
            $structure->food_allowance +
            $structure->other_allowances
        );
    }

    // ─── توليد رقم الموظف ─────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $last = static::orderByDesc('id')->value('employee_number');
        $next = $last ? (int) substr($last, 3) + 1 : 1;
        return 'EMP' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // ─── حساب مكافأة نهاية الخدمة ────────────────────────────────────

    public function gratuityAmount(): float
    {
        $years   = $this->hire_date->diffInYears(now());
        $monthly = (float) $this->basic_salary;

        if ($years < 1) return 0;
        if ($years <= 5) return $monthly * $years * 0.5;
        return ($monthly * 5 * 0.5) + ($monthly * ($years - 5));
    }
}
