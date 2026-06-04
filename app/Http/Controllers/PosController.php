<?php

// المسار الكامل: app/Http/Controllers/PosController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\ExchangeRate;
use App\Models\PosSession;
use App\Models\PosTransaction;
use App\Models\Product;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(protected PosService $posService) {}

    // ─── شاشة الكاشير الرئيسية ────────────────────────────────────

    public function index()
    {
        $session     = PosSession::currentOpen();
        $categories  = Category::whereNull('parent_id')->orderBy('name_ar')->get();
        $currentRate = ExchangeRate::currentRate();

        if (! $session) {
            return view('pos.open-session');
        }

        return view('pos.index', compact('session', 'categories', 'currentRate'));
    }

    // ─── API — بحث المنتجات ───────────────────────────────────────

    public function searchProducts(Request $request): JsonResponse
    {
        $q          = $request->get('q', '');
        $categoryId = $request->get('category_id');
        $page       = max(1, (int) $request->get('page', 1));
        $perPage    = 24; // عدد البطاقات في كل صفحة (3-4 صفوف)

        $query = Product::query()->where('is_active', true);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name_ar', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', $q);
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $currentRate = ExchangeRate::currentRate();

        $paginated = $query->with('category')
            ->orderByRaw('quantity > 0 DESC')
            ->orderBy('name_ar')
            ->paginate($perPage, ['*'], 'page', $page);

        $products = $paginated->getCollection()->map(fn($p) => [
            'id'           => $p->id,
            'name'         => $p->name_ar,
            'sku'          => $p->sku ?? '',
            'barcode'      => $p->barcode ?? '',
            'price_usd'    => (float) $p->price_usd,
            'sale_price'   => $p->sale_price_sdg,
            'quantity'     => (int) $p->quantity,
            'image_url'    => $p->image_url,
            'category'     => $p->category?->name_ar ?? '',
            'stock_status' => $p->quantity <= 0 ? 'out_of_stock' : ($p->quantity <= ($p->reorder_point ?: 5) ? 'low' : 'ok'),
            'description'  => $p->description ?? '',
            'cost_price'   => (float) $p->purchase_price_usd,
            'unit'         => $p->unit ?? '',
            'exchange_rate'=> $currentRate,
        ]);

        return response()->json([
            'products'      => $products,
            'count'         => $products->count(),
            'hasMore'       => $paginated->hasMorePages(),
            'nextPage'      => $paginated->hasMorePages() ? $page + 1 : null,
            'total'         => $paginated->total(),
            'exchange_rate' => $currentRate,
        ]);
    }

    // ─── API — بحث بالباركود ─────────────────────────────────────

    public function findByBarcode(Request $request): JsonResponse
    {
        $barcode = trim($request->barcode ?? '');

        if (! $barcode) {
            return response()->json(['found' => false]);
        }

        $product = Product::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json(['found' => false, 'message' => 'المنتج غير موجود']);
        }

        if ($product->quantity <= 0) {
            return response()->json(['found' => false, 'message' => "المنتج «{$product->name_ar}» نفد من المخزون"]);
        }

        $currentRate = ExchangeRate::currentRate();

        return response()->json([
            'found'   => true,
            'product' => [
                'id'           => $product->id,
                'name'         => $product->name_ar,
                'sku'          => $product->sku,
                'price_usd'    => (float) $product->price_usd,
                'sale_price'   => $product->sale_price_sdg,   // الجنيه للكاشير
                'quantity'     => (int) $product->quantity,
                'image_url'    => $product->image_url,
                'exchange_rate'=> $currentRate,
            ],
        ]);
    }

    // ─── API — بحث العملاء ────────────────────────────────────────

    public function searchCustomers(Request $request): JsonResponse
    {
        $q = $request->q ?? '';

        $customers = Customer::query()
            ->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'phone', 'balance']);

        return response()->json(['customers' => $customers]);
    }

    // ─── إتمام عملية البيع ───────────────────────────────────────

    public function processSale(Request $request): JsonResponse
    {
        $request->validate([
            'session_id'               => ['required', 'exists:pos_sessions,id'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['required', 'exists:products,id'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.001'],
            'items.*.price'            => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment.payment_type'     => ['required', 'in:cash,credit,split,bank_transfer'],
            'payment.cash_received'    => ['nullable', 'numeric', 'min:0'],
            'payment.bank_ref_number'  => ['nullable', 'string', 'max:100'],
            'payment.bank_name'        => ['nullable', 'string', 'max:100'],
            'payment.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment.tax_percent'      => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $session = PosSession::findOrFail($request->session_id);

        if ($session->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'هذه الجلسة ليست لك'], 403);
        }

        try {
            $transaction = $this->posService->processSale(
                session: $session,
                cartItems: $request->items,
                payData: $request->payment,
                customerId: $request->customer_id,
            );

            return response()->json([
                'success'            => true,
                'message'            => 'تمت عملية البيع بنجاح',
                'transaction_id'     => $transaction->id,
                'receipt_number'     => $transaction->receipt_number,
                'receipt_url'        => route('pos.receipt', $transaction),
                'invoice_print_url'  => $transaction->invoice_id ? route('invoices.print', $transaction->invoice_id) : null,
                'invoice_id'         => $transaction->invoice_id,
                'total'              => $transaction->total,
                'change_amount'      => $transaction->change_amount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─── الإيصال ─────────────────────────────────────────────────

    public function receipt(PosTransaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user', 'session']);
        $settings    = \App\Models\Setting::pluck('value', 'key');
        $currentRate = ExchangeRate::currentRate();

        return view('pos.receipt', compact('transaction', 'settings', 'currentRate'));
    }

    public function reprint(PosTransaction $transaction)
    {
        return $this->receipt($transaction);
    }

    // ─── إلغاء معاملة ────────────────────────────────────────────

    public function cancelTransaction(PosTransaction $transaction): JsonResponse
    {
        if ($transaction->session->user_id !== auth()->id() && ! auth()->user()->hasRole('مدير عام')) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        try {
            $this->posService->cancelTransaction($transaction);
            return response()->json(['success' => true, 'message' => 'تم إلغاء المعاملة وإعادة المخزون']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─── تقارير POS ──────────────────────────────────────────────

    public function report(Request $request)
    {
        $date   = $request->date ?? today()->toDateString();
        $userId = $request->user_id;

        $query = PosTransaction::with(['items.product', 'customer', 'user', 'session'])
            ->completed()
            ->whereDate('created_at', $date);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $transactions = $query->latest()->get();

        $summary = [
            'total_sales'        => $transactions->sum('total'),
            'total_cash'         => $transactions->sum('cash_amount'),
            'total_credit'       => $transactions->sum('credit_amount'),
            'total_discount'     => $transactions->sum('discount_amount'),
            'transactions_count' => $transactions->count(),
            'top_products'       => $this->topProductsForDate($date),
        ];

        $cashiers = \App\Models\User::whereHas('posSessions', function ($q) use ($date) {
            $q->whereDate('opened_at', $date);
        })->get(['id', 'name']);

        return view('pos.report', compact('transactions', 'summary', 'date', 'cashiers', 'userId'));
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function topProductsForDate(string $date): \Illuminate\Support\Collection
    {
        return \App\Models\PosTransactionItem::query()
            ->join('pos_transactions', 'pos_transaction_items.transaction_id', '=', 'pos_transactions.id')
            ->join('products', 'pos_transaction_items.product_id', '=', 'products.id')
            ->whereDate('pos_transactions.created_at', $date)
            ->where('pos_transactions.status', 'completed')
            ->selectRaw('products.name_ar, SUM(pos_transaction_items.quantity) as qty_sold, SUM(pos_transaction_items.total) as revenue')
            ->groupBy('products.id', 'products.name_ar')
            ->orderByDesc('qty_sold')
            ->limit(10)
            ->get();
    }
}
