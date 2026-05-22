<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ─── بطاقات إحصائية ──────────────────────────────────────────

        // مبيعات اليوم
        $salesToday = Invoice::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // المخزون الحرج (أقل من حد الطلب)
        $criticalStockCount = Product::whereColumn('quantity', '<=', 'reorder_point')
            ->where('quantity', '>', 0)
            ->count();

        // المنتجات المنتهية
        $outOfStockCount = Product::where('quantity', 0)->count();

        // المستحقات (فواتير آجلة غير مسددة)
        $totalReceivables = Invoice::whereIn('status', ['partial', 'confirmed'])
            ->where('type', 'credit')
            ->sum(DB::raw('total - COALESCE((SELECT SUM(amount) FROM payments WHERE invoice_id = invoices.id), 0)'));

        // إجمالي الرواتب الشهر الحالي (سيُكمَل في المرحلة 7)
        $monthlyPayroll = 0;

        // ─── رسم بياني: مبيعات آخر 12 شهر ──────────────────────────

        $monthExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';
        $yearExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', created_at) AS INTEGER)"
            : 'YEAR(created_at)';

        $monthlySales = Invoice::selectRaw("{$monthExpr} as month, {$yearExpr} as year, SUM(total) as total")
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->groupByRaw("{$yearExpr}, {$monthExpr}")
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(fn($row) => [
                'label' => $this->monthName($row->month) . ' ' . $row->year,
                'total' => (float) $row->total,
            ]);

        // ─── رسم بياني: أكثر المنتجات مبيعاً ───────────────────────

        $topProducts = DB::table('invoice_items')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.status', '!=', 'cancelled')
            ->where('invoices.created_at', '>=', now()->subDays(30))
            ->selectRaw('products.name_ar, SUM(invoice_items.quantity) as total_qty')
            ->groupBy('products.id', 'products.name_ar')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        // ─── آخر 10 فواتير ───────────────────────────────────────────

        $latestInvoices = Invoice::with('customer')
            ->latest()
            ->limit(10)
            ->get();

        // ─── آخر 10 طلبات مشتريات ────────────────────────────────────

        $latestPurchaseOrders = PurchaseOrder::with('supplier')
            ->latest()
            ->limit(10)
            ->get();

        // ─── تنبيهات ─────────────────────────────────────────────────

        $alerts = [];

        // منتجات نفدت
        if ($outOfStockCount > 0) {
            $alerts[] = [
                'type'    => 'danger',
                'icon'    => 'exclamation-triangle',
                'message' => "{$outOfStockCount} منتج نفد من المخزون",
                'link'    => route('products.index') . '?filter=out_of_stock',
            ];
        }

        // منتجات حرجة
        if ($criticalStockCount > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'icon'    => 'exclamation-circle',
                'message' => "{$criticalStockCount} منتج وصل للحد الأدنى",
                'link'    => route('products.index') . '?filter=critical',
            ];
        }

        // فواتير متأخرة السداد
        $overdueInvoices = Invoice::where('status', 'confirmed')
            ->where('type', 'credit')
            ->where('due_date', '<', today())
            ->count();

        if ($overdueInvoices > 0) {
            $alerts[] = [
                'type'    => 'danger',
                'icon'    => 'clock',
                'message' => "{$overdueInvoices} فاتورة متأخرة السداد",
                'link'    => route('invoices.index') . '?filter=overdue',
            ];
        }

        return view('dashboard.index', compact(
            'salesToday',
            'criticalStockCount',
            'outOfStockCount',
            'totalReceivables',
            'monthlyPayroll',
            'monthlySales',
            'topProducts',
            'latestInvoices',
            'latestPurchaseOrders',
            'alerts',
            'overdueInvoices',
        ));
    }

    // ─── Helper: اسم الشهر عربي ──────────────────────────────────────

    private function monthName(int $month): string
    {
        $months = [
            1  => 'يناير', 2  => 'فبراير', 3  => 'مارس',
            4  => 'أبريل', 5  => 'مايو',   6  => 'يونيو',
            7  => 'يوليو', 8  => 'أغسطس',  9  => 'سبتمبر',
            10 => 'أكتوبر',11 => 'نوفمبر', 12 => 'ديسمبر',
        ];
        return $months[$month] ?? '';
    }
}
