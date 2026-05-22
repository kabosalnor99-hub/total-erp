<?php

// المسار الكامل: app/Models/Leave.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    protected $fillable = [
        'employee_id', 'approved_by', 'type',
        'start_date', 'end_date', 'days',
        'reason', 'status', 'rejection_reason', 'approved_at',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'date',
    ];

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'annual'    => 'سنوية',
            'sick'      => 'مرضية',
            'emergency' => 'طارئة',
            'unpaid'    => 'بدون راتب',
            'other'     => 'أخرى',
            default     => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'قيد الانتظار',
            'approved' => 'معتمدة',
            'rejected' => 'مرفوضة',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-600',
            default    => 'bg-gray-100 text-gray-600',
        };
    }
}
