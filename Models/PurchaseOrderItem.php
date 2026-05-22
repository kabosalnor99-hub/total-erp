<?php

// المسار الكامل: app/Models/PurchaseOrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'received_quantity',
        'unit_price',
        'discount',
        'total',
        'notes',
    ];

    protected $casts = [
        'quantity'          => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'unit_price'        => 'decimal:2',
        'discount'          => 'decimal:2',
        'total'             => 'decimal:2',
    ];

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getRemainingQuantityAttribute(): float
    {
        return (float) ($this->quantity - $this->received_quantity);
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }
}
