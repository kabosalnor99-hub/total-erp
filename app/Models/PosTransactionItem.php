<?php

// المسار الكامل: app/Models/PosTransactionItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'unit_price',
        'price',
        'discount_percent',
        'discount_amount',
        'total',
    ];

    protected $casts = [
        'quantity'         => 'decimal:3',
        'unit_price'       => 'decimal:2',
        'price'            => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total'            => 'decimal:2',
    ];

    // ─── العلاقات ────────────────────────────────────────────────

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PosTransaction::class, 'transaction_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getProfitAttribute(): float
    {
        $cost = (float)$this->product?->purchase_price * (float)$this->quantity;
        return (float)$this->total - $cost;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->total <= 0) return 0;
        return round(($this->profit / $this->total) * 100, 2);
    }
}
