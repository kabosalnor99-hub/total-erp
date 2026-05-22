<?php

// المسار الكامل: app/Models/SupplierPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $fillable = [
        'payment_number',
        'supplier_id',
        'purchase_order_id',
        'user_id',
        'amount',
        'method',
        'reference',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ─── توليد رقم الدفعة ───────────────────────────────────────────

    public static function generateNumber(): string
    {
        $last = static::latest('id')->value('payment_number');
        $next = $last ? ((int) substr($last, 3)) + 1 : 1;
        return 'SP-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'cash'          => 'نقدي',
            'bank_transfer' => 'تحويل بنكي',
            'check'         => 'شيك',
            'other'         => 'أخرى',
            default         => $this->method,
        };
    }

    public function getMethodColorAttribute(): string
    {
        return match ($this->method) {
            'cash'          => 'success',
            'bank_transfer' => 'info',
            'check'         => 'warning',
            default         => 'secondary',
        };
    }
}
