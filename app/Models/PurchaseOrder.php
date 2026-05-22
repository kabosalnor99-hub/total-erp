<?php

// المسار الكامل: app/Models/PurchaseOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'order_number',
        'supplier_id',
        'user_id',
        'purchase_request_id',
        'status',
        'subtotal',
        'discount',
        'tax',
        'total',
        'amount_paid',
        'expected_date',
        'notes',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'tax'           => 'decimal:2',
        'total'         => 'decimal:2',
        'amount_paid'   => 'decimal:2',
        'expected_date' => 'date',
    ];

    // ─── توليد رقم الأمر ────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $last = static::latest('id')->value('order_number');
        $next = $last ? ((int) substr($last, 3)) + 1 : 1;
        return 'PO-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'      => 'مسودة',
            'sent'       => 'أُرسل للمورد',
            'partial'    => 'استلام جزئي',
            'received'   => 'مستلم بالكامل',
            'cancelled'  => 'ملغي',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'secondary',
            'sent'      => 'info',
            'partial'   => 'warning',
            'received'  => 'success',
            'cancelled' => 'danger',
            default     => 'secondary',
        };
    }

    public function getRemainingAmountAttribute(): float
    {
        return (float) ($this->total - $this->amount_paid);
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopeSearch($query, string $term)
    {
        return $query->where('order_number', 'like', "%{$term}%")
                     ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$term}%"));
    }
}
