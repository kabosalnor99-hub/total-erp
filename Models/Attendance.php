<?php

// المسار الكامل: app/Models/Attendance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'late_minutes',
        'overtime_minutes',
        'status',
        'notes',
    ];

    protected $casts = [
        'date'             => 'date',
        'late_minutes'     => 'integer',
        'overtime_minutes' => 'integer',
    ];

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->whereMonth('date', $month)->whereYear('date', $year);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'حاضر',
            'absent'   => 'غائب',
            'late'     => 'متأخر',
            'on_leave' => 'في إجازة',
            'holiday'  => 'إجازة رسمية',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'bg-green-100 text-green-700',
            'absent'   => 'bg-red-100 text-red-600',
            'late'     => 'bg-yellow-100 text-yellow-700',
            'on_leave' => 'bg-blue-100 text-blue-700',
            'holiday'  => 'bg-gray-100 text-gray-600',
            default    => 'bg-gray-100 text-gray-600',
        };
    }

    public function getWorkingHoursAttribute(): float
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        $in  = Carbon::parse($this->check_in);
        $out = Carbon::parse($this->check_out);

        return round($out->diffInMinutes($in) / 60, 2);
    }

    public function getLateMinutesLabelAttribute(): string
    {
        if ($this->late_minutes <= 0) return '-';
        $h = intdiv($this->late_minutes, 60);
        $m = $this->late_minutes % 60;
        return $h > 0 ? "{$h}س {$m}د" : "{$m}د";
    }

    public function getOvertimeMinutesLabelAttribute(): string
    {
        if ($this->overtime_minutes <= 0) return '-';
        $h = intdiv($this->overtime_minutes, 60);
        $m = $this->overtime_minutes % 60;
        return $h > 0 ? "{$h}س {$m}د" : "{$m}د";
    }
}
