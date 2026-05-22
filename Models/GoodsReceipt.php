<?php

// المسار الكامل: app/Models/GoodsReceipt.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'warehouse_id',
        'user_id',
        'status',
        'received_date',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    // ─── توليد رقم الاستلام ─────────────────────────────────────────

    public static function generateNumber(): string
    {
        $last = static::latest('id')->value('receipt_number');
        $next = $last ? ((int) substr($last, 3)) + 1 : 1;
        return 'GR-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'confirmed' ? 'مؤكد' : 'مسودة';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status === 'confirmed' ? 'success' : 'warning';
    }

    /** القيمة الإجمالية للاستلام */
    public function totalValue(): float
    {
        return (float) $this->items->sum(fn($i) => $i->quantity_received * $i->unit_price);
    }
}
