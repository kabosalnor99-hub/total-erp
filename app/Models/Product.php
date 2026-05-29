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
        'purchase_price_usd',   // سعر الشراء بالدولار (الاسم الجديد بعد rename)
        'price_usd',            // سعر البيع بالدولار  (الاسم الجديد بعد rename)
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
        'purchase_price_usd' => 'decimal:2',
        'price_usd'          => 'decimal:2',
        'profit_margin'      => 'decimal:2',
        'quantity'           => 'integer',
        'reorder_point'      => 'integer',
        'images'             => 'array',
        'is_active'          => 'boolean',
    ];

    // ─── العلاقات ────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCritical($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_point')
                     ->where('quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    public function scopeStagnant($query)
    {
        return $query->whereDoesntHave('stockMovements', function ($q) {
            $q->where('created_at', '>=', now()->subDays(90));
        });
    }

    // ─── Accessors — أساسية ──────────────────────────────────────────

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar'
            ? $this->name_ar
            : ($this->name_en ?? $this->name_ar);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            $imagePath = public_path($this->image);
            if (file_exists($imagePath)) {
                return asset($this->image);
            }
        }
        return "https://via.placeholder.com/200x200/f3f4f6/6b7280?text=" . urlencode($this->name_ar);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity <= 0)                    return 'out_of_stock';
        if ($this->quantity <= $this->reorder_point) return 'critical';
        return 'available';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'نفد المخزون',
            'critical'     => 'مخزون حرج',
            default        => 'متوفر',
        };
    }

    public function getStockStatusColorAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'danger',
            'critical'     => 'warning',
            default        => 'success',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        $types = [
            'power_tools' => 'أدوات كهربائية',
            'hand_tools'  => 'أدوات يدوية',
            'equipment'   => 'معدات',
            'spare_parts' => 'قطع غيار',
        ];
        return $types[$this->type] ?? 'أخرى';
    }

    // ─── Accessors — الأسعار USD / SDG ───────────────────────────────

    /**
     * سعر البيع بالجنيه السوداني (يُحسب من USD × سعر الصرف)
     */
    public function getSalePriceSdgAttribute(): float
    {
        $rate = ExchangeRate::currentRate();
        return $rate > 0 ? round((float) $this->price_usd * $rate, 2) : (float) $this->price_usd;
    }

    /**
     * سعر الشراء بالجنيه السوداني
     */
    public function getPurchasePriceSdgAttribute(): float
    {
        $rate = ExchangeRate::currentRate();
        return $rate > 0 ? round((float) $this->purchase_price_usd * $rate, 2) : (float) $this->purchase_price_usd;
    }

    /**
     * هامش الربح بالدولار
     */
    public function getProfitUsdAttribute(): float
    {
        return round((float) $this->price_usd - (float) $this->purchase_price_usd, 2);
    }

    /**
     * سعر البيع منسَّق — USD
     */
    public function getFormattedPriceUsdAttribute(): string
    {
        return '$' . number_format($this->price_usd, 2);
    }

    /**
     * سعر البيع منسَّق — SDG
     */
    public function getFormattedSalePriceSdgAttribute(): string
    {
        return number_format($this->sale_price_sdg, 0) . ' ج.س';
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public static function calcProfitMargin(float $purchase, float $sale): float
    {
        if ($purchase <= 0) return 0;
        return round((($sale - $purchase) / $purchase) * 100, 2);
    }

    public static function generateSku(string $prefix = 'TL'): string
    {
        $last = self::withTrashed()->latest('id')->value('id') ?? 0;
        return $prefix . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
