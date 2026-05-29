<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ─── البيانات الثابتة نسبياً → تُكاش 5 دقائق ─────────────────
        $stats = Cache::remember(
            CacheService::dashboardKey(),
            CacheService::TTL_DASHBOARD,
            function () {
                // مبيعات اليوم
                $salesToday = Invoice::whereDate('created_at', today())
                    ->where('status', '!=', 'cancelled')
                    ->sum('total');

                // المخزون الحرج
                $criticalStockCount = Product::whereColumn('quantity', '<=', 'reorder_point')
                    ->where('quantity', '>', 0)
                    ->count();

                // المنتجات المنتهية
                $outOfStockCount = Product::where('quantity', 0)->count();

                // المستحقات
                $totalReceivables = Invoice::whereIn('status', ['partial', 'confirmed'])
                    ->where('type', 'credit')
                    ->sum(DB::raw('total - COALESCE((SELECT SUM(amount) FROM payments WHERE invoice_id = invoices.id), 0)'));

                // رسم بياني: مبيعات آخر 12 شهر
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

                // رسم بياني: أكثر المنتجات مبيعاً (آخر 30 يوم)
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

                // فواتير متأخرة السداد
                $overdueInvoices = Invoice::where('status', 'confirmed')
                    ->where('type', 'credit')
                    ->where('due_date', '<', today())
                    ->count();

                return compact(
                    'salesToday', 'criticalStockCount', 'outOfStockCount',
                    'totalReceivables', 'monthlySales', 'topProducts', 'overdueInvoices'
                );
            }
        );

        // ─── استخراج المتغيرات من الـ cache ──────────────────────────
        extract($stats);
        $monthlyPayroll = 0;

        // ─── آخر السجلات: لا تُكاش (تتغير باستمرار) ─────────────────
        $latestInvoices = Invoice::with('customer')->latest()->limit(10)->get();
        $latestPurchaseOrders = PurchaseOrder::with('supplier')->latest()->limit(10)->get();

        // ─── تنبيهات ─────────────────────────────────────────────────
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
