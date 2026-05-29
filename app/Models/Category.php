<?php

// المسار الكامل: app/Models/Category.php

namespace App\Models;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'parent_id',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // ─── Cache ───────────────────────────────────────────────────────

    /**
     * جلب الفئات النشطة مع فئاتها الفرعية من الـ cache
     * يُستخدم في القوائم المنسدلة وصفحات المنتجات
     */
    public static function cachedActive(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            CacheService::categoriesKey(),
            CacheService::TTL_CATEGORIES,
            fn () => self::with('children')
                ->active()
                ->root()
                ->orderBy('sort_order')
                ->orderBy('name_ar')
                ->get()
        );
    }

    /**
     * مسح الـ cache تلقائياً عند أي تعديل على الفئات
     */
    protected static function booted(): void
    {
        $flush = fn () => CacheService::forgetCategories();

        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }

    // ─── العلاقات ────────────────────────────────────────────────────

    /** الفئة الأب */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /** الفئات الفرعية */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /** المنتجات في هذه الفئة */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    /** الفئات الرئيسية فقط (بدون أب) */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /** الفئات النشطة فقط */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Accessors ───────────────────────────────────────────────────

    /** الاسم حسب اللغة الحالية */
    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? $this->name_ar
            : ($this->name_en ?? $this->name_ar);
    }

    /** عدد المنتجات في الفئة */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }
}
