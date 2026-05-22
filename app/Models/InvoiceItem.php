<?php

// المسار الكامل: app/Models/InvoiceItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'product_name',
        'product_sku',
        'unit',
        'quantity',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'total',
    ];

    protected $casts = [
        'quantity'         => 'decimal:3',
        'unit_price'       => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total'            => 'decimal:2',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /** حساب إجمالي البند */
    public function calculateTotal(): float
    {
        $subtotal = $this->quantity * $this->unit_price;

        $discount = $this->discount_percent > 0
            ? round($subtotal * $this->discount_percent / 100, 2)
            : $this->discount_amount;

        return round($subtotal - $discount, 2);
    }
}
