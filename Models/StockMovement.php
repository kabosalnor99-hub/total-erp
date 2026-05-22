<?php

// المسار الكامل: app/Models/StockMovement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'reference_type',
        'reference_id',
        'warehouse_to_id',
        'user_id',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'quantity_before' => 'integer',
        'quantity_after'  => 'integer',
        'unit_cost'       => 'decimal:2',
    ];

    // ─── العلاقات ────────────────────────────────────────────────────

    /** المنتج */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** المستودع المصدر */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** المستودع الوجهة (للتحويلات) */
    public function warehouseTo(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_to_id');
    }

    /** المستخدم المسؤول */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeIncoming($query)
    {
        return $query->whereIn('type', ['in', 'return_in']);
    }

    public function scopeOutgoing($query)
    {
        return $query->whereIn('type', ['out', 'return_out']);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    // ─── Accessors ───────────────────────────────────────────────────

    /** نوع الحركة بالعربية */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in'         => 'إضافة مخزون',
            'out'        => 'إخراج مخزون',
            'transfer'   => 'تحويل بين مستودعات',
            'adjust'     => 'تسوية مخزون',
            'return_in'  => 'مرتجع شراء',
            'return_out' => 'مرتجع بيع',
            default      => $this->type,
        };
    }

    /** لون الحركة */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'in', 'return_in'  => 'success',
            'out', 'return_out' => 'danger',
            'transfer'          => 'info',
            'adjust'            => 'warning',
            default             => 'secondary',
        };
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    /**
     * تسجيل حركة مخزون وتحديث الكمية في المنتج
     */
    public static function record(
        int $productId,
        int $warehouseId,
        string $type,
        int $quantity,
        array $extra = []
    ): self {
        $product = Product::findOrFail($productId);
        $before  = $product->quantity;

        // تحديث كمية المنتج
        if (in_array($type, ['in', 'return_in'])) {
            $product->increment('quantity', $quantity);
        } elseif (in_array($type, ['out', 'return_out'])) {
            $product->decrement('quantity', $quantity);
        } elseif ($type === 'adjust') {
            $product->update(['quantity' => $quantity]);
        }

        $after = $product->fresh()->quantity;

        return self::create(array_merge([
            'product_id'      => $productId,
            'warehouse_id'    => $warehouseId,
            'type'            => $type,
            'quantity'        => $quantity,
            'quantity_before' => $before,
            'quantity_after'  => $after,
            'user_id'         => auth()->id(),
        ], $extra));
    }
}
