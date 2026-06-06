<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Services\AiContextService;
use App\Models\AiInsight;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(private AiService $ai)
    {
        $this->middleware(['auth']);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  نقاط النهاية (Endpoints)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * POST /ai/ask
     * سؤال حر من الواجهة
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'prompt'  => 'required|string|max:2000',
            'context' => 'nullable|array',
            'fresh'   => 'nullable|boolean',
        ]);

        $response = $request->boolean('fresh')
            ? $this->ai->askFresh($request->prompt, $request->context ?? [])
            : $this->ai->ask($request->prompt, $request->context ?? []);

        return response()->json(['response' => $response]);
    }

    /**
     * POST /ai/chat
     * محادثة متعددة الأدوار
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'messages'        => 'required|array|min:1',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.text' => 'required|string|max:4000',
            'context'         => 'nullable|array',
        ]);

        $response = $this->ai->chat($request->messages, $request->context ?? []);

        return response()->json(['response' => $response]);
    }

    /**
     * GET /ai/inventory-alert
     * تنبيهات المخزون الذكية
     */
    public function inventoryAlert(): JsonResponse
    {
        $products = \App\Models\Product::select('name_ar as name', 'quantity as qty', 'reorder_point as reorder')
            ->whereColumn('quantity', '<=', 'reorder_point')
            ->orWhere('quantity', 0)
            ->orderBy('quantity')
            ->limit(20)
            ->get()
            ->toArray();

        if (empty($products)) {
            return response()->json(['response' => 'المخزون في وضع جيد — لا توجد منتجات تحتاج تنبيهاً حالياً.']);
        }

        return response()->json(['response' => $this->ai->inventoryAlert($products)]);
    }

    /**
     * GET /ai/sales-insight
     * تحليل المبيعات
     */
    public function salesInsight(): JsonResponse
    {
        $today     = \App\Models\Invoice::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total');
        $lastWeek  = \App\Models\Invoice::whereBetween('created_at', [now()->subDays(7), now()])->where('status', '!=', 'cancelled')->sum('total');

        $topProducts = \Illuminate\Support\Facades\DB::table('invoice_items')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.status', '!=', 'cancelled')
            ->where('invoices.created_at', '>=', now()->subDays(30))
            ->selectRaw('products.name_ar as name, SUM(invoice_items.quantity) as total_qty')
            ->groupBy('products.id', 'products.name_ar')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get()
            ->toArray();

        return response()->json([
            'response' => $this->ai->salesInsight([
                'today'       => $today,
                'last_7_days' => $lastWeek,
                'top_products' => $topProducts,
            ]),
        ]);
    }

    /**
     * GET /ai/purchase-forecast
     * توقع الشراء
     */
    public function purchaseForecast(): JsonResponse
    {
        $stock = \App\Models\Product::select('name_ar as name', 'quantity', 'reorder_point')
            ->where('quantity', '>', 0)
            ->orderBy('quantity')
            ->limit(30)
            ->get()
            ->toArray();

        $salesTrend = \Illuminate\Support\Facades\DB::table('invoice_items')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.created_at', '>=', now()->subDays(14))
            ->where('invoices.status', '!=', 'cancelled')
            ->selectRaw('products.name_ar as name, AVG(invoice_items.quantity) as avg_daily_qty')
            ->groupBy('products.id', 'products.name_ar')
            ->orderByDesc('avg_daily_qty')
            ->limit(15)
            ->get()
            ->toArray();

        return response()->json([
            'response' => $this->ai->purchaseForecast($stock, $salesTrend),
        ]);
    }

    /**
     * GET /ai/analysis/{module}
     * تحليل تلقائي حسب الموديول مع حفظ النتيجة
     */
    public function autoAnalysis(string $module, AiContextService $context): JsonResponse
    {
        $allowed = ['pos', 'sales', 'inventory', 'customers', 'general'];

        if (!in_array($module, $allowed)) {
            return response()->json(['error' => 'موديول غير معروف'], 422);
        }

        $ctx    = $context->getContext($module);
        $prompt = $context->getAutoPrompt($module);
        $result = $this->ai->askFresh($prompt, $ctx);

        // حفظ في قاعدة البيانات للرجوع إليها لاحقاً
        try {
            DB::table('ai_insights')->insert([
                'module'     => $module,
                'type'       => 'analysis',
                'content'    => $result,
                'date'       => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // لا نوقف الرد إذا فشل الحفظ
        }

        return response()->json(['response' => $result, 'module' => $module]);
    }

}
