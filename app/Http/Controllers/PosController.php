<?php

// المسار الكامل: app/Http/Controllers/PosController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PosSession;
use App\Models\PosTransaction;
use App\Models\Product;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(protected PosService $posService) {}

    // ─── الشاشة الرئيسية للكاشير ─────────────────────────────────

    /**
     * شاشة الكاشير الرئيسية
     */
    public function index()
    {
        $session    = PosSession::currentOpen();
        $categories = Category::where('is_active', true)->orderBy('name_ar')->get();

        if (! $session) {
            return view('pos.open-session');
        }

        return view('pos.index', compact('session', 'categories'));
    }

    // ─── API — بحث المنتجات ───────────────────────────────────────

    /**
     * البحث عن المنتجات (AJAX)
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $q          = $request->q ?? '';
        $categoryId = $request->category_id;

        $query = Product::query()
            ->where('is_active', true);

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name_ar', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', $q);
            });
        }

        if ($categoryId) {
            // Get the category and its children
            $category = Category::find($categoryId);
            if ($category) {
                $categoryIds = [$categoryId];
                if ($category->children()->exists()) {
                    $categoryIds = array_merge($categoryIds, $category->children()->pluck('id')->toArray());
                }
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $categoryId);
            }
        }

        $products = $query->with('category')
            ->orderBy('name_ar')
            ->limit(100)
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'name'         => $p->name_ar,
                'sku'          => $p->sku,
                'barcode'      => $p->barcode,
                'sale_price'   => (float) $p->sale_price,
                'quantity'     => (int) $p->quantity,
                'image_url'    => $p->image_url,
                'category'     => $p->category?->name_ar,
                'stock_status' => $p->stock_status,
            ]);

        return response()->json(['products' => $products]);
    }

    /**
     * البحث بالباركود (AJAX)
     */
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

        return response()->json([
            'found'   => true,
            'product' => [
                'id'         => $product->id,
                'name'       => $product->name_ar,
                'sku'        => $product->sku,
                'sale_price' => (float) $product->sale_price,
                'quantity'   => (int) $product->quantity,
                'image_url'  => $product->image_url,
            ],
        ]);
    }

    // ─── API — بحث العملاء ────────────────────────────────────────

    /**
     * البحث عن العملاء (AJAX)
     */
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

    /**
     * معالجة الفاتورة من الكاشير (AJAX)
     */
    public function processSale(Request $request): JsonResponse
    {
        $request->validate([
            'session_id'               => ['required', 'exists:pos_sessions,id'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['required', 'exists:products,id'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.001'],
            'items.*.price'            => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment.payment_type'     => ['required', 'in:cash,credit,split'],
            'payment.cash_received'    => ['nullable', 'numeric', 'min:0'],
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
                'success'        => true,
                'message'        => 'تمت عملية البيع بنجاح',
                'transaction_id' => $transaction->id,
                'receipt_number' => $transaction->receipt_number,
                'receipt_url'    => route('pos.receipt', $transaction),
                'change_amount'  => $transaction->change_amount,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * حفظ فاتورة مبدئية (AJAX)
     */
    public function saveDraftInvoice(Request $request): JsonResponse
    {
        $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.001'],
            'items.*.price'      => ['required', 'numeric', 'min:0'],
            'customer_id'        => ['nullable', 'exists:customers,id'],
            'discount_percent'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_percent'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'              => ['nullable', 'string'],
        ]);

        try {
            \DB::beginTransaction();

            // حساب المجاميع
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['price'];
            }

            $discountAmount = $request->discount_percent > 0
                ? round($subtotal * $request->discount_percent / 100, 2)
                : 0;

            $afterDiscount = $subtotal - $discountAmount;

            $taxAmount = $request->tax_percent > 0
                ? round($afterDiscount * $request->tax_percent / 100, 2)
                : 0;

            $total = $afterDiscount + $taxAmount;

            // إنشاء الفاتورة المبدئية
            $invoice = Invoice::create([
                'invoice_number'   => Invoice::generateNumber(),
                'customer_id'      => $request->customer_id,
                'user_id'          => auth()->id(),
                'type'             => 'credit',
                'status'           => 'draft',
                'subtotal'         => $subtotal,
                'discount_amount'  => $discountAmount,
                'discount_percent' => $request->discount_percent ?? 0,
                'tax_percent'      => $request->tax_percent ?? 0,
                'tax_amount'       => $taxAmount,
                'total'            => $total,
                'paid_amount'      => 0,
                'remaining_amount' => $total,
                'notes'            => $request->notes,
            ]);

            // إضافة عناصر الفاتورة
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                $itemTotal = $item['quantity'] * $item['price'];
                $itemDiscount = $item['discount_percent'] ?? 0;
                $itemDiscountAmount = round($itemTotal * $itemDiscount / 100, 2);
                $finalPrice = $itemTotal - $itemDiscountAmount;

                InvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'product_id'       => $item['product_id'],
                    'product_name'     => $product->name_ar,
                    'product_sku'      => $product->sku,
                    'unit'             => $product->unit,
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['price'],
                    'discount_percent' => $itemDiscount,
                    'discount_amount'  => $itemDiscountAmount,
                    'price'            => $finalPrice,
                    'total'            => $finalPrice,
                ]);
            }

            \DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'تم حفظ الفاتورة المبدئية بنجاح',
                'invoice_id'   => $invoice->id,
                'invoice_url'  => route('invoices.show', $invoice),
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─── الإيصال ─────────────────────────────────────────────────

    /**
     * عرض إيصال حراري
     */
    public function receipt(PosTransaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user', 'session']);

        $settings = \App\Models\Setting::pluck('value', 'key');

        return view('pos.receipt', compact('transaction', 'settings'));
    }

    /**
     * إعادة طباعة إيصال سابق
     */
    public function reprint(PosTransaction $transaction)
    {
        return $this->receipt($transaction);
    }

    // ─── إلغاء معاملة ────────────────────────────────────────────

    /**
     * إلغاء معاملة (AJAX)
     */
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

    /**
     * تقرير مبيعات POS اليومي
     */
    public function report(Request $request)
    {
        $date    = $request->date ?? today()->toDateString();
        $userId  = $request->user_id;

        $query = PosTransaction::with(['items.product', 'customer', 'user', 'session'])
            ->completed()
            ->whereDate('created_at', $date);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $transactions = $query->latest()->get();

        $summary = [
            'total_sales'       => $transactions->sum('total'),
            'total_cash'        => $transactions->sum('cash_amount'),
            'total_credit'      => $transactions->sum('credit_amount'),
            'total_discount'    => $transactions->sum('discount_amount'),
            'transactions_count'=> $transactions->count(),
            'top_products'      => $this->topProductsForDate($date),
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
