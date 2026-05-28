<?php

namespace App\Models;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'location', 'manager_name',
        'phone', 'is_active', 'is_default', 'notes',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    // ─── العلاقات ────────────────────────────────────────────────────

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'warehouse_to_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Cache ───────────────────────────────────────────────────────

    /**
     * جيب المستودعات النشطة من الـ cache
     */
    public static function cachedActive(): \Illuminate\Support\Collection
    {
        return Cache::remember(
            CacheService::warehousesKey(),
            CacheService::TTL_WAREHOUSES,
            fn() => static::active()->get()
        );
    }

    /**
     * المستودع الافتراضي (مع cache)
     */
    public static function getDefault(): ?self
    {
        return static::cachedActive()->firstWhere('is_default', true)
            ?? static::cachedActive()->first();
    }

    // ─── إلغاء الـ cache تلقائياً ────────────────────────────────────

    protected static function booted(): void
    {
        static::saved(fn()   => CacheService::forgetWarehouses());
        static::deleted(fn() => CacheService::forgetWarehouses());
    }

    // ─── Accessors ───────────────────────────────────────────────────

    public function getTotalItemsAttribute(): int
    {
        return StockMovement::where('warehouse_id', $this->id)
            ->selectRaw("SUM(CASE WHEN type IN ('in','return_in','adjust') THEN quantity
                              WHEN type IN ('out','return_out') THEN -quantity
                              ELSE 0 END) as total")
            ->value('total') ?? 0;
    }
}
