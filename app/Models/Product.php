<?php

// المسار الكامل: app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'barcode',
        'name_ar',
        'name_en',
        'category_id',
        'brand',
        'unit',
        'purchase_price',
        'sale_price',
        'profit_margin',
        'quantity',
        'reorder_point',
        'image',
        'images',
        'type',
        'is_active',
        'description',
        'created_by',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price'     => 'decimal:2',
        'profit_margin'  => 'decimal:2',
        'quantity'       => 'integer',
        'reorder_point'  => 'integer',
        'images'         => 'array',
        'is_active'      => 'boolean',
    ];

    // ─── العلاقات ────────────────────────────────────────────────────

    /** الفئة */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** المنشئ */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** حركات المخزون */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    /** منتجات نشطة */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** منتجات حرجة (وصلت للحد الأدنى) */
    public function scopeCritical($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_point')
                     ->where('quantity', '>', 0);
    }

    /** منتجات نفدت */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    /** منتجات راكدة (لم تتحرك 90 يوم) */
    public function scopeStagnant($query)
    {
        return $query->whereDoesntHave('stockMovements', function ($q) {
            $q->where('created_at', '>=', now()->subDays(90));
        });
    }

    // ─── Accessors ───────────────────────────────────────────────────

    /** الاسم حسب اللغة الحالية */
    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? $this->name_ar
            : ($this->name_en ?? $this->name_ar);
    }

    /** رابط الصورة */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            $imagePath = public_path($this->image);
            // Check if file exists locally, otherwise use placeholder
            if (file_exists($imagePath)) {
                return asset($this->image);
            }
        }
        // Use placeholder image from external service
        return "https://via.placeholder.com/200x200/f3f4f6/6b7280?text=" . urlencode($this->name_ar);
    }

    /** حالة المخزون */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        }
        if ($this->quantity <= $this->reorder_point) {
            return 'critical';
        }
        return 'available';
    }

    /** تسمية حالة المخزون بالعربية */
    public function getStockStatusLabelAttribute(): string
    {
        $labels = [
            'out_of_stock' => 'نفد المخزون',
            'critical'     => 'مخزون حرج',
        ];
        return $labels[$this->stock_status] ?? 'متوفر';
    }

    /** لون badge حالة المخزون */
    public function getStockStatusColorAttribute(): string
    {
        $colors = [
            'out_of_stock' => 'danger',
            'critical'     => 'warning',
        ];
        return $colors[$this->stock_status] ?? 'success';
    }

    /** نوع المنتج بالعربية */
    public function getTypeLabelAttribute(): string
    {
        $types = [
            'power_tools'  => 'أدوات كهربائية',
            'hand_tools'   => 'أدوات يدوية',
            'equipment'    => 'معدات',
            'spare_parts'  => 'قطع غيار',
        ];
        return $types[$this->type] ?? 'أخرى';
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    /** حساب هامش الربح تلقائياً */
    public static function calcProfitMargin(float $purchase, float $sale): float
    {
        if ($purchase <= 0) {
            return 0;
        }
        return round((($sale - $purchase) / $purchase) * 100, 2);
    }

    /** إنشاء SKU تلقائي */
    public static function generateSku(string $prefix = 'TL'): string
    {
        $last = self::withTrashed()->latest('id')->value('id') ?? 0;
        return $prefix . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
