<?php

// المسار الكامل: app/Models/PurchaseRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'request_number',
        'user_id',
        'approved_by',
        'status',
        'needed_by',
        'notes',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'needed_by'   => 'date',
        'approved_at' => 'datetime',
    ];

    // ─── توليد رقم الطلب ────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $last = static::latest('id')->value('request_number');
        $next = $last ? ((int) substr($last, 3)) + 1 : 1;
        return 'PR-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'في الانتظار',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
            'ordered'  => 'تم الطلب',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'ordered'  => 'info',
            default    => 'secondary',
        };
    }

    /** إجمالي القيمة التقديرية */
    public function estimatedTotal(): float
    {
        return (float) $this->items->sum(fn($i) => $i->quantity * $i->estimated_price);
    }
}
