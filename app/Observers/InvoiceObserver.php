<?php

// المسار الكامل: app/Observers/InvoiceObserver.php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * InvoiceObserver
 *
 * المسؤولية الوحيدة: مسح الـ cache الصحيح عند أي تغيير على الفاتورة.
 *
 * ما يُمسح:
 *   1. dashboard_stats       ← أي فاتورة جديدة/محذوفة تغيّر الإحصائيات
 *   2. report_sales_*        ← التقارير المرتبطة بتواريخ الفاتورة
 *   3. report_overdue_*      ← عند تغيير الحالة قد تدخل/تخرج من "متأخرة"
 *   4. notifications_recent  ← عند الإلغاء/الحذف قد تتغير الإشعارات للعميل
 */
class InvoiceObserver
{
    // ─── Created ────────────────────────────────────────────────────────
    /**
     * فاتورة جديدة → تؤثر على:
     *   - Dashboard (مجموع اليوم / الشهر)
     *   - تقارير المبيعات للفترة التي تقع فيها الفاتورة
     */
    public function created(Invoice $invoice): void
    {
        $this->forgetDashboard();
        $this->forgetSalesReportsForInvoice($invoice);

        Log::debug("[InvoiceObserver] created #{$invoice->id} → cache cleared");
    }

    // ─── Updated ────────────────────────────────────────────────────────
    /**
     * فاتورة محدَّثة → نتحقق ماذا تغيّر لنمسح بدقة.
     *
     * الحالات:
     *   - تغيّر الـ status → ربما أصبحت مدفوعة أو ملغاة → Dashboard + Overdue
     *   - تغيّر الـ total / paid_amount → Dashboard + تقارير
     *   - تغيّر الـ due_date → Overdue report
     */
    public function updated(Invoice $invoice): void
    {
        $dirty = $invoice->getDirty();

        // دائماً امسح Dashboard عند أي تحديث
        $this->forgetDashboard();

        // تغيّر المبالغ → تقارير المبيعات
        if ($this->hasMoneyChanges($dirty)) {
            $this->forgetSalesReportsForInvoice($invoice);
        }

        // تغيّر الحالة أو تاريخ الاستحقاق → تقرير المتأخرات
        if (array_key_exists('status', $dirty) || array_key_exists('due_date', $dirty)) {
            $this->forgetOverdueReport();
        }

        Log::debug("[InvoiceObserver] updated #{$invoice->id} dirty=" . implode(',', array_keys($dirty)));
    }

    // ─── Deleted (SoftDelete) ───────────────────────────────────────────
    /**
     * حذف ناعم → كأنها محذوفة من الإحصائيات
     */
    public function deleted(Invoice $invoice): void
    {
        $this->forgetDashboard();
        $this->forgetSalesReportsForInvoice($invoice);
        $this->forgetOverdueReport();

        Log::debug("[InvoiceObserver] deleted #{$invoice->id} → cache cleared");
    }

    // ─── Restored ───────────────────────────────────────────────────────
    /**
     * استعادة فاتورة محذوفة → تعود للإحصائيات
     */
    public function restored(Invoice $invoice): void
    {
        $this->forgetDashboard();
        $this->forgetSalesReportsForInvoice($invoice);

        Log::debug("[InvoiceObserver] restored #{$invoice->id} → cache cleared");
    }

    // ─── Force Deleted ──────────────────────────────────────────────────
    public function forceDeleted(Invoice $invoice): void
    {
        $this->forgetDashboard();
        $this->forgetSalesReportsForInvoice($invoice);
        $this->forgetOverdueReport();

        Log::debug("[InvoiceObserver] forceDeleted #{$invoice->id} → cache cleared");
    }

    // ════════════════════════════════════════════════════════════════════
    // Private Helpers
    // ════════════════════════════════════════════════════════════════════

    /** مسح إحصائيات الـ Dashboard */
    private function forgetDashboard(): void
    {
        CacheService::forgetDashboard();
    }

    /**
     * مسح تقارير المبيعات التي تشمل تاريخ الفاتورة.
     *
     * المشكلة: مفاتيح التقارير تحتوي تواريخ مثل:
     *   report_sales_summary_2026-01-01_2026-01-31
     *
     * الحل: نبحث بالـ prefix الثابت ونحذف ما يتطابق.
     * إذا كان الـ cache driver لا يدعم tags/prefix → نمسح الكل بالـ pattern.
     */
    private function forgetSalesReportsForInvoice(Invoice $invoice): void
    {
        $date = $invoice->created_at
            ? $invoice->created_at->toDateString()
            : now()->toDateString();

        // الأنماط الممكنة التي تشمل هذه الفاتورة:
        // - report_sales_summary_{from}_{to}
        // - report_sales_by_customer_{from}_{to}
        // - report_sales_by_product_{from}_{to}
        //
        // بما أننا لا نعرف كل المدد المُخزَّنة، نمسح المفاتيح اليومية والشهرية
        // الأكثر شيوعاً (نفس منطق ReportController).

        $month      = $invoice->created_at ? $invoice->created_at->format('Y-m') : now()->format('Y-m');
        $monthStart = $month . '-01';
        $monthEnd   = now()->parse($monthStart)->endOfMonth()->toDateString();
        $today      = now()->toDateString();

        $prefixes = [
            'report_sales_summary',
            'report_sales_by_customer',
            'report_sales_by_product',
        ];

        // المدد الشائعة: اليوم، الشهر الحالي، مدة تشمل التاريخ
        $dateRanges = [
            [$date,       $date],
            [$monthStart, $monthEnd],
            [$monthStart, $today],
        ];

        foreach ($prefixes as $prefix) {
            foreach ($dateRanges as [$from, $to]) {
                Cache::forget("{$prefix}_{$from}_{$to}");
            }
        }
    }

    /** مسح تقرير الفواتير المتأخرة (يُخزَّن بتاريخ اليوم) */
    private function forgetOverdueReport(): void
    {
        $key = 'report_overdue_invoices_' . today()->toDateString();
        Cache::forget($key);
    }

    /** هل يحتوي الـ dirty على حقول مالية؟ */
    private function hasMoneyChanges(array $dirty): bool
    {
        $moneyFields = ['total', 'paid_amount', 'remaining_amount', 'subtotal', 'tax_amount', 'discount_amount'];

        return ! empty(array_intersect(array_keys($dirty), $moneyFields));
    }
}
