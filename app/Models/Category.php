<?php

// المسار الكامل: app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
