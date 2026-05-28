<?php

namespace App\Models;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    protected $fillable = ['name_ar', 'name_en', 'description', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    // ─── العلاقات ────────────────────────────────────────────────

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Cache ───────────────────────────────────────────────────

    /**
     * جيب الفئات النشطة من الـ cache — تُستخدم في Dropdowns
     */
    public static function cachedActive(): \Illuminate\Support\Collection
    {
        return Cache::remember(
            CacheService::categoriesKey(),
            CacheService::TTL_CATEGORIES,
            fn() => static::active()->orderBy('name_ar')->get()
        );
    }

    // ─── إلغاء الـ cache تلقائياً عند الحفظ/الحذف ────────────────

    protected static function booted(): void
    {
        static::saved(fn()   => CacheService::forgetCategories());
        static::deleted(fn() => CacheService::forgetCategories());
    }
}
