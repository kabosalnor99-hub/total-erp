<?php

// المسار الكامل: app/Observers/ProductObserver.php

namespace App\Observers;

use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

/**
 * ProductObserver
 *
 * ما يُمسح:
 *   1. product_stats   ← إحصائيات المنتجات (عدد النشطة، نفد المخزون، الحرجة)
 *   2. dashboard_stats ← Dashboard يعرض ملخص المنتجات والمخزون
 *
 * ملاحظة: تغيير الكمية (quantity) يحدث عبر StockMovement::record()
 * الذي يستدعي increment/decrement مباشرة على المنتج → هذا الـ Observer
 * سيُفعَّل تلقائياً لأن updated() يُطلَق عند أي save/update.
 */
class ProductObserver
{
    public function created(Product $product): void
    {
        $this->flushAll();
        Log::debug("[ProductObserver] created #{$product->id}");
    }

    public function updated(Product $product): void
    {
        $dirty = $product->getDirty();

        // دائماً امسح product_stats
        CacheService::forgetProductStats();

        // تغيّر الكمية أو السعر أو الحالة → امسح Dashboard أيضاً
        $affectsDashboard = ['quantity', 'is_active', 'price_usd', 'reorder_point'];
        if (! empty(array_intersect(array_keys($dirty), $affectsDashboard))) {
            CacheService::forgetDashboard();
        }

        Log::debug("[ProductObserver] updated #{$product->id} dirty=" . implode(',', array_keys($dirty)));
    }

    public function deleted(Product $product): void
    {
        $this->flushAll();
        Log::debug("[ProductObserver] deleted #{$product->id}");
    }

    public function restored(Product $product): void
    {
        $this->flushAll();
        Log::debug("[ProductObserver] restored #{$product->id}");
    }

    public function forceDeleted(Product $product): void
    {
        $this->flushAll();
        Log::debug("[ProductObserver] forceDeleted #{$product->id}");
    }

    // ─── Helpers ────────────────────────────────────────────────────
    private function flushAll(): void
    {
        // forgetProductStats تمسح product_stats + dashboard_stats معاً
        CacheService::forgetProductStats();
    }
}
