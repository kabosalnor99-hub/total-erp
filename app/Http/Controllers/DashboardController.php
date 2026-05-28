<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ─── إحصائيات Dashboard (cache 5 دقائق) ──────────────────────
        $stats = Cache::remember(
            CacheService::dashboardKey(),
            CacheService::TTL_DASHBOARD,
            fn() => $this->buildDashboardStats()
        );

        // آخر 10 فواتير (cache أقصر — 1 دقيقة لأنها تتغير كثيراً)
        $latestInvoices = Cache::remember('dashboard_latest_invoices', 60, function () {
            return Invoice::with('customer')->latest()->limit(10)->get();
        });

        // آخر 10 طلبات شراء
        $latestPurchaseOrders = Cache::remember('dashboard_latest_pos', 60, function () {
            return PurchaseOrder::with('supplier')->latest()->limit(10)->get();
        });

        return view('dashboard.index', array_merge($stats, compact(
            'latestInvoices',
            'latestPurchaseOrders',
        )));
    }

    // ─── بناء إحصائيات Dashboard ──────────────────────────────────────

    private function buildDashboardStats(): array
    {
        $salesToday = Invoice::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $criticalStockCount = Product::whereColumn('quantity', '<=', 'reorder_point')
            ->where('quantity', '>', 0)
            ->count();

        $outOfStockCount = Product::where('quantity', 0)->count();

        $totalReceivables = Invoice::whereIn('status', ['partial', 'confirmed'])
            ->where('type', 'credit')
            ->sum(DB::raw('total - COALESCE((SELECT SUM(amount) FROM payments WHERE invoice_id = invoices.id), 0)'));

        $monthlyPayroll = 0;

        // رسم بياني مبيعات 12 شهر
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
            ->orderBy('year')->orderBy('month')
            ->get()
            ->map(fn($row) => [
                'label' => $this->monthName($row->month) . ' ' . $row->year,
                'total' => (float) $row->total,
            ]);

        // أكثر المنتجات مبيعاً
        $topProducts = DB::table('invoice_items')
            ->join('products',  'invoice_items.product_id',  '=', 'products.id')
            ->join('invoices',  'invoice_items.invoice_id',  '=', 'invoices.id')
            ->where('invoices.status', '!=', 'cancelled')
            ->where('invoices.created_at', '>=', now()->subDays(30))
            ->selectRaw('products.name_ar, SUM(invoice_items.quantity) as total_qty')
            ->groupBy('products.id', 'products.name_ar')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        // تنبيهات
        $alerts = [];

        if ($outOfStockCount > 0) {
            $alerts[] = [
                'type'    => 'danger',
                'icon'    => 'exclamation-triangle',
                'message' => "{$outOfStockCount} منتج نفد من المخزون",
                'link'    => route('products.index') . '?filter=out_of_stock',
            ];
        }

        if ($criticalStockCount > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'icon'    => 'exclamation-circle',
                'message' => "{$criticalStockCount} منتج وصل للحد الأدنى",
                'link'    => route('products.index') . '?filter=critical',
            ];
        }

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

        return compact(
            'salesToday', 'criticalStockCount', 'outOfStockCount',
            'totalReceivables', 'monthlyPayroll', 'monthlySales',
            'topProducts', 'alerts', 'overdueInvoices'
        );
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس',
            4 => 'أبريل', 5 => 'مايو',   6 => 'يونيو',
            7 => 'يوليو', 8 => 'أغسطس',  9 => 'سبتمبر',
            10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ][$month] ?? '';
    }
}
