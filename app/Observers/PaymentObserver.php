<?php

// المسار الكامل: app/Observers/PaymentObserver.php

namespace App\Observers;

use App\Models\Payment;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * PaymentObserver
 *
 * الدفعة تؤثر مباشرة على:
 *   1. dashboard_stats           ← إجمالي المدفوعات اليوم/الشهر
 *   2. report_sales_summary      ← تقرير المبيعات يحسب paid_amount
 *   3. report_overdue_invoices   ← دفعة قد تُخرج الفاتورة من المتأخرات
 *
 * ملاحظة: Payment لا يدعم SoftDeletes → نتجاهل restored/forceDeleted.
 */
class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $this->flushAll($payment);
        Log::debug("[PaymentObserver] created #{$payment->id} amount={$payment->amount}");
    }

    public function updated(Payment $payment): void
    {
        $this->flushAll($payment);
        Log::debug("[PaymentObserver] updated #{$payment->id}");
    }

    public function deleted(Payment $payment): void
    {
        $this->flushAll($payment);
        Log::debug("[PaymentObserver] deleted #{$payment->id}");
    }

    // ─── Helpers ────────────────────────────────────────────────────

    private function flushAll(Payment $payment): void
    {
        // Dashboard
        CacheService::forgetDashboard();

        // تقارير المبيعات للفترة التي تقع فيها الدفعة
        $this->forgetSalesReports($payment);

        // تقرير المتأخرات (الدفعة قد تغيّر حالة الفاتورة)
        $this->forgetOverdueReport();
    }

    private function forgetSalesReports(Payment $payment): void
    {
        // تاريخ الدفعة (payment_date هو الأهم، وإلا created_at)
        $date = $payment->payment_date
            ? $payment->payment_date->toDateString()
            : ($payment->created_at ? $payment->created_at->toDateString() : now()->toDateString());

        $month      = now()->parse($date)->format('Y-m');
        $monthStart = $month . '-01';
        $monthEnd   = now()->parse($monthStart)->endOfMonth()->toDateString();
        $today      = now()->toDateString();

        $prefixes = [
            'report_sales_summary',
            'report_sales_by_customer',
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

    private function forgetOverdueReport(): void
    {
        Cache::forget('report_overdue_invoices_' . today()->toDateString());
    }
}
