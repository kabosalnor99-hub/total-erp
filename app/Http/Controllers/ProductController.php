<?php

// المسار الكامل: app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProductController extends Controller
{
    // ─── قائمة المنتجات ──────────────────────────────────────────────

    public function index(Request $request): View|JsonResponse
    {
        $query = Product::with('category')->latest();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($w) use ($q) {
                $w->where('name_ar', 'like', "%{$q}%")
                  ->orWhere('name_en', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%")
                  ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('filter')) {
            match ($request->filter) {
                'out_of_stock' => $query->outOfStock(),
                'critical'     => $query->critical(),
                'stagnant'     => $query->stagnant(),
                default        => null,
            };
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->paginate(20)->withQueryString();

        // ── AJAX: إرجاع JSON لطلبات Infinite Scroll ──────────────────────
        if ($request->ajax()) {
            $html = '';
            foreach ($products as $product) {
                $html .= view('products._product_row', compact('product'))->render();
            }

            return response()->json([
                'html'     => $html,
                'hasMore'  => $products->hasMorePages(),
                'nextPage' => $products->currentPage() + 1,
                'total'    => $products->total(),
                'showing'  => $products->lastItem(),
            ]);
        }

        // ── طلب عادي: عرض الصفحة الكاملة ────────────────────────────────
        $categories = Category::cachedActive();

        $stats = Cache::remember(
            CacheService::productStatsKey(),
            CacheService::TTL_STATS,
            fn () => [
                'total'        => Product::count(),
                'active'       => Product::active()->count(),
                'out_of_stock' => Product::outOfStock()->count(),
                'critical'     => Product::critical()->count(),
            ]
        );

        $currentRate = ExchangeRate::currentRate();

        return view('products.index', compact('products', 'categories', 'stats', 'currentRate'));
    }

    // ─── عرض منتج واحد ───────────────────────────────────────────────

    public function show(Product $product): View
    {
        $product->load('category', 'creator');

        $movements = StockMovement::with('warehouse', 'user')
            ->where('product_id', $product->id)
            ->latest()
            ->paginate(15);

        $warehouses = Warehouse::active()->get()->map(function ($w) use ($product) {
            $w->current_stock = StockMovement::where('product_id', $product->id)
                ->where('warehouse_id', $w->id)
                ->selectRaw("SUM(CASE WHEN type IN ('in','return_in') THEN quantity
                                  WHEN type IN ('out','return_out') THEN -quantity
                                  ELSE 0 END) as stock")
                ->value('stock') ?? 0;
            return $w;
        });

        $currentRate = ExchangeRate::currentRate();

        return view('products.show', compact('product', 'movements', 'warehouses', 'currentRate'));
    }

    // ─── نموذج إنشاء منتج ────────────────────────────────────────────

    public function create(): View
    {
        $categories  = Category::cachedActive();
        $warehouses  = Warehouse::cachedActive();
        $nextSku     = Product::generateSku();
        $currentRate = ExchangeRate::currentRate();

        return view('products.create', compact('categories', 'warehouses', 'nextSku', 'currentRate'));
    }

    // ─── حفظ منتج جديد ───────────────────────────────────────────────

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        if ($request->hasFile('extra_images')) {
            $extraImages = [];
            foreach ($request->file('extra_images') as $img) {
                $extraImages[] = $img->store('products', 'public');
            }
            $data['images'] = $extraImages;
        }

        // حساب هامش الربح من USD مباشرة
        $data['profit_margin'] = Product::calcProfitMargin(
            (float) ($data['purchase_price_usd'] ?? 0),
            (float) ($data['price_usd'] ?? 0)
        );

        $data['created_by'] = auth()->id();

        $product = Product::create($data);

        if (!empty($data['initial_quantity']) && $data['initial_quantity'] > 0) {
            $warehouseId = $data['warehouse_id'] ?? Warehouse::getDefault()?->id;
            if ($warehouseId) {
                StockMovement::record(
                    $product->id,
                    $warehouseId,
                    'in',
                    (int) $data['initial_quantity'],
                    ['notes' => 'مخزون ابتدائي عند إنشاء المنتج']
                );
            }
        }

        ActivityLog::record('created', "إضافة منتج: {$product->name_ar}", $product);
        CacheService::forgetProductStats();

        return redirect()->route('products.show', $product)
            ->with('success', 'تم إضافة المنتج بنجاح.');
    }

    // ─── نموذج تعديل منتج ────────────────────────────────────────────

    public function edit(Product $product): View
    {
        $categories  = Category::cachedActive();
        $product->load('category');
        $currentRate = ExchangeRate::currentRate();

        return view('products.edit', compact('product', 'categories', 'currentRate'));
    }

    // ─── تحديث منتج ──────────────────────────────────────────────────

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $priceUsd    = (float) ($data['price_usd']          ?? $product->price_usd);
        $purchaseUsd = (float) ($data['purchase_price_usd'] ?? $product->purchase_price_usd);

        $data['profit_margin'] = Product::calcProfitMargin($purchaseUsd, $priceUsd);

        $old = $product->only(['name_ar', 'price_usd', 'purchase_price_usd', 'quantity']);
        $product->update($data);

        ActivityLog::record('updated', "تعديل منتج: {$product->name_ar}", $product, $old);
        CacheService::forgetProductStats();

        return redirect()->route('products.show', $product)
            ->with('success', 'تم تحديث المنتج بنجاح.');
    }

    // ─── حذف منتج ────────────────────────────────────────────────────

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->quantity > 0) {
            return back()->with('error', 'لا يمكن حذف منتج له مخزون. قم بتصفية المخزون أولاً.');
        }

        ActivityLog::record('deleted', "حذف منتج: {$product->name_ar}", $product);
        $product->delete();
        CacheService::forgetProductStats();

        return redirect()->route('products.index')
            ->with('success', 'تم حذف المنتج بنجاح.');
    }

    // ─── تسوية المخزون ───────────────────────────────────────────────

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason'       => 'required|string|max:255',
        ]);

        StockMovement::record(
            $product->id,
            $request->warehouse_id,
            'adjust',
            $request->new_quantity,
            ['reason' => $request->reason]
        );

        ActivityLog::record('adjusted', "تسوية مخزون: {$product->name_ar}", $product);

        return back()->with('success', 'تم تسوية المخزون بنجاح.');
    }

    // ─── بحث AJAX ────────────────────────────────────────────────────

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $currentRate = ExchangeRate::currentRate();

        $products = Product::with('category')
            ->where(function ($w) use ($query) {
                $w->where('name_ar', 'like', "%{$query}%")
                  ->orWhere('name_en', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($product) => [
                'id'                 => $product->id,
                'sku'                => $product->sku,
                'name_ar'            => $product->name_ar,
                'name_en'            => $product->name_en,
                'image'              => $product->image_url,
                'price_usd'          => (float) $product->price_usd,
                'sale_price_sdg'     => $product->sale_price_sdg,
                'purchase_price_usd' => (float) $product->purchase_price_usd,
                'purchase_price_sdg' => $product->purchase_price_sdg,
                'quantity'           => $product->quantity,
                'category'           => $product->category?->name_ar,
                'exchange_rate'      => $currentRate,
            ]);

        return response()->json(['results' => $products]);
    }
}
