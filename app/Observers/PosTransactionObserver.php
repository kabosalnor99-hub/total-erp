<?php

// المسار الكامل: app/Observers/PosTransactionObserver.php

namespace App\Observers;

use App\Models\PosTransaction;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * PosTransactionObserver
 *
 * معاملة POS تؤثر على:
 *   1. dashboard_stats         ← مبيعات اليوم/الشهر
 *   2. report_sales_summary    ← تقرير المبيعات الإجمالي
 *   3. report_sales_by_product ← تقرير مبيعات المنتجات
 *
 * ملاحظة: POS لا يدعم SoftDeletes.
 * الإلغاء يمر عبر update على status → يُطلق updated().
 */
class PosTransactionObserver
{
    public function created(PosTransaction $transaction): void
    {
        // فقط المعاملات المكتملة تؤثر على الإحصائيات
        if ($transaction->status === 'completed') {
            $this->flushAll($transaction);
        }
        Log::debug("[PosTransactionObserver] created #{$transaction->id} status={$transaction->status}");
    }

    public function updated(PosTransaction $transaction): void
    {
        $dirty = $transaction->getDirty();

        // تغيّر الحالة أو المبلغ → امسح الكل
        if (array_key_exists('status', $dirty) || array_key_exists('total', $dirty)) {
            $this->flushAll($transaction);
        }

        Log::debug("[PosTransactionObserver] updated #{$transaction->id} dirty=" . implode(',', array_keys($dirty)));
    }

    public function deleted(PosTransaction $transaction): void
    {
        $this->flushAll($transaction);
        Log::debug("[PosTransactionObserver] deleted #{$transaction->id}");
    }

    // ─── Helpers ────────────────────────────────────────────────────

    private function flushAll(PosTransaction $transaction): void
    {
        CacheService::forgetDashboard();
        $this->forgetSalesReports($transaction);
    }

    private function forgetSalesReports(PosTransaction $transaction): void
    {
        $date = $transaction->created_at
            ? $transaction->created_at->toDateString()
            : now()->toDateString();

        $month      = now()->parse($date)->format('Y-m');
        $monthStart = $month . '-01';
        $monthEnd   = now()->parse($monthStart)->endOfMonth()->toDateString();
        $today      = now()->toDateString();

        $prefixes = [
            'report_sales_summary',
            'report_sales_by_product',
        ];

        $ranges = [
            [$date,       $date],
            [$monthStart, $monthEnd],
            [$monthStart, $today],
        ];

        foreach ($prefixes as $prefix) {
            foreach ($ranges as [$from, $to]) {
                Cache::forget("{$prefix}_{$from}_{$to}");
            }
        }
    }
}
