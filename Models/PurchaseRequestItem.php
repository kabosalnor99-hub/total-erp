<?php

// المسار الكامل: app/Models/PurchaseRequestItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'product_id',
        'quantity',
        'estimated_price',
        'notes',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'estimated_price' => 'decimal:2',
    ];

    // ─── العلاقات ─────────────────────────────────────────────────────

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->quantity * $this->estimated_price);
    }
}
