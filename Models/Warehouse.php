<?php

// المسار الكامل: app/Models/Warehouse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'manager_name',
        'phone',
        'is_active',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    // ─── العلاقات ────────────────────────────────────────────────────

    /** حركات المخزون في هذا المستودع */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /** حركات التحويل الواردة */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'warehouse_to_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Accessors / Helpers ─────────────────────────────────────────

    /** المستودع الافتراضي */
    public static function getDefault(): ?self
    {
        return self::where('is_default', true)->first()
            ?? self::where('is_active', true)->first();
    }

    /** إجمالي الكميات في هذا المستودع */
    public function getTotalItemsAttribute(): int
    {
        return StockMovement::where('warehouse_id', $this->id)
            ->selectRaw("SUM(CASE WHEN type IN ('in','return_in','adjust') THEN quantity
                              WHEN type IN ('out','return_out') THEN -quantity
                              ELSE 0 END) as total")
            ->value('total') ?? 0;
    }
}
