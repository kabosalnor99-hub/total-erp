<?php

// المسار: app/Models/ExchangeRate.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExchangeRate extends Model
{
    protected $fillable = [
        'rate',
        'effective_date',
        'notes',
        'is_active',
        'previous_rate',
        'change_percent',
        'created_by',
    ];

    protected $casts = [
        'rate'            => 'decimal:4',
        'previous_rate'   => 'decimal:4',
        'change_percent'  => 'decimal:2',
        'is_active'       => 'boolean',
        'effective_date'  => 'date',
    ];

    // ─── العلاقات ────────────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLatest30($query)
    {
        return $query->orderBy('effective_date', 'desc')->limit(30);
    }

    // ─── Static Helpers ──────────────────────────────────────────────

    /**
     * الحصول على سعر الصرف الحالي (مع Cache)
     */
    public static function getCurrent(): float
    {
        return (float) Cache::rememberForever('current_exchange_rate', function () {
            $rate = static::where('is_active', true)
                          ->orderBy('effective_date', 'desc')
                          ->value('rate');
            return $rate ?? 1.0;
        });
    }

    /**
     * Alias لـ getCurrent() — للاستخدام في الـ Views والـ Models
     */
    public static function currentRate(): float
    {
        return static::getCurrent();
    }

    /**
     * مسح الـ Cache عند تغيير السعر
     */
    public static function clearCache(): void
    {
        Cache::forget('current_exchange_rate');
        Cache::forget('exchange_rate_stats');
    }

    /**
     * تفعيل سعر جديد وتحديث أسعار جميع المنتجات
     *
     * @param  float  $newRate   سعر الصرف الجديد (SDG/USD)
     * @param  string $date      تاريخ السريان
     * @param  string $notes     ملاحظات
     * @param  int    $userId    معرف المستخدم
     * @return static
     */
    public static function activateNew(float $newRate, string $date, string $notes, int $userId): static
    {
        return DB::transaction(function () use ($newRate, $date, $notes, $userId) {

            // السعر الحالي قبل التغيير
            $previousRate = static::getCurrent();

            // نسبة التغيير
            $changePercent = $previousRate > 0
                ? round((($newRate - $previousRate) / $previousRate) * 100, 2)
                : 0;

            // إلغاء تفعيل جميع الأسعار السابقة
            static::where('is_active', true)->update(['is_active' => false]);

            // إنشاء السعر الجديد
            $exchangeRate = static::create([
                'rate'           => $newRate,
                'effective_date' => $date,
                'notes'          => $notes,
                'is_active'      => true,
                'previous_rate'  => $previousRate,
                'change_percent' => $changePercent,
                'created_by'     => $userId,
            ]);

            // ─── تحديث أسعار جميع المنتجات ─────────────────────────
            // sale_price (SDG) = price_usd * newRate
            DB::statement('
                UPDATE products
                SET
                    sale_price     = ROUND(price_usd * ?, 2),
                    purchase_price = ROUND(purchase_price_usd * ?, 2),
                    profit_margin  = CASE
                        WHEN purchase_price_usd > 0
                        THEN ROUND(((price_usd - purchase_price_usd) / purchase_price_usd) * 100, 2)
                        ELSE 0
                    END,
                    updated_at     = NOW()
                WHERE price_usd IS NOT NULL AND price_usd > 0
            ', [$newRate, $newRate]);

            // مسح الـ Cache
            static::clearCache();
            Cache::forget('all_settings_grouped');

            return $exchangeRate;
        });
    }

    /**
     * تحويل مبلغ من USD إلى SDG
     */
    public static function toSDG(float $usd): float
    {
        return round($usd * static::getCurrent(), 2);
    }

    /**
     * تحويل مبلغ من SDG إلى USD
     */
    public static function toUSD(float $sdg): float
    {
        $rate = static::getCurrent();
        return $rate > 0 ? round($sdg / $rate, 4) : 0;
    }

    /**
     * إحصائيات سعر الصرف للتقارير
     */
    public static function getStats(): array
    {
        return Cache::remember('exchange_rate_stats', 3600, function () {
            $rates = static::orderBy('effective_date', 'desc')->limit(30)->get();

            if ($rates->isEmpty()) {
                return [];
            }

            $current  = $rates->first();
            $previous = $rates->skip(1)->first();

            return [
                'current'          => (float) $current->rate,
                'previous'         => $previous ? (float) $previous->rate : null,
                'change_percent'   => (float) $current->change_percent,
                'highest_30d'      => (float) $rates->max('rate'),
                'lowest_30d'       => (float) $rates->min('rate'),
                'avg_30d'          => round((float) $rates->avg('rate'), 2),
                'last_updated'     => $current->effective_date->format('Y-m-d'),
                'updates_count_30d'=> $rates->count(),
            ];
        });
    }

    // ─── Accessors ───────────────────────────────────────────────────

    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate, 2) . ' ج.س / $';
    }

    public function getDirectionAttribute(): string
    {
        if (!$this->change_percent) return 'stable';
        return $this->change_percent > 0 ? 'up' : 'down';
    }
}
