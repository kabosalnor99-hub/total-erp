<?php

// المسار الكامل: app/Observers/StockMovementObserver.php

namespace App\Observers;

use App\Models\StockMovement;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

/**
 * StockMovementObserver
 *
 * حركة المخزون تؤثر على:
 *   1. product_stats   ← كمية المنتج تغيّرت (نفد/حرج/متوفر)
 *   2. dashboard_stats ← Dashboard يعرض إحصائيات المخزون
 *
 * ملاحظة: StockMovement لا يدعم SoftDeletes ولا يُحذف عادةً
 * (سجل محاسبي)، لكن نضيف deleted() للأمان.
 *
 * ملاحظة مهمة: عند استدعاء StockMovement::record() يحدث:
 *   1. product->increment/decrement → يُطلق ProductObserver::updated()
 *   2. StockMovement::create()      → يُطلق هذا الـ Observer
 * كلاهما يمسحان product_stats + dashboard → لا مشكلة (Cache::forget
 * على key غير موجود لا تُسبب خطأ).
 */
class StockMovementObserver
{
    public function created(StockMovement $movement): void
    {
        $this->flushAll();
        Log::debug("[StockMovementObserver] created #{$movement->id} type={$movement->type} qty={$movement->quantity}");
    }

    public function updated(StockMovement $movement): void
    {
        // نادراً ما يُعدَّل سجل حركة، لكن نتعامل معه للأمان
        $this->flushAll();
        Log::debug("[StockMovementObserver] updated #{$movement->id}");
    }

    public function deleted(StockMovement $movement): void
    {
        $this->flushAll();
        Log::debug("[StockMovementObserver] deleted #{$movement->id}");
    }

    // ─── Helpers ────────────────────────────────────────────────────
    private function flushAll(): void
    {
        // forgetProductStats تمسح product_stats + dashboard_stats معاً
        CacheService::forgetProductStats();
    }
}
