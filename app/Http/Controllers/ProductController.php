<?php

// المسار الكامل: app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    // ─── قائمة المنتجات ──────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Product::with('category')->latest();

        // البحث
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($w) use ($q) {
                $w->where('name_ar', 'like', "%{$q}%")
                  ->orWhere('name_en', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%")
                  ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        // فلتر الفئة
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // فلتر النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فلتر حالة المخزون
        if ($request->filled('filter')) {
            match ($request->filter) {
                'out_of_stock' => $query->outOfStock(),
                'critical'     => $query->critical(),
                'stagnant'     => $query->stagnant(),
                default        => null,
            };
        }

        // فلتر الحالة
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products   = $query->paginate(100)->withQueryString();
        $categories = Category::active()->orderBy('name_ar')->get();

        // إحصائيات سريعة
        $stats = [
            'total'         => Product::count(),
            'active'        => Product::active()->count(),
            'out_of_stock'  => Product::outOfStock()->count(),
            'critical'      => Product::critical()->count(),
        ];

        return view('products.index', compact('products', 'categories', 'stats'));
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

        return view('products.show', compact('product', 'movements', 'warehouses'));
    }

    // ─── نموذج إنشاء منتج ────────────────────────────────────────────

    public function create(): View
    {
        $categories = Category::active()->orderBy('name_ar')->get();
        $warehouses = Warehouse::active()->get();
        $nextSku    = Product::generateSku();

        return view('products.create', compact('categories', 'warehouses', 'nextSku'));
    }

    // ─── حفظ منتج جديد ───────────────────────────────────────────────

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // رفع الصورة الرئيسية
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // رفع صور إضافية
        if ($request->hasFile('extra_images')) {
            $extraImages = [];
            foreach ($request->file('extra_images') as $img) {
                $extraImages[] = $img->store('products', 'public');
            }
            $data['images'] = $extraImages;
        }

        // حساب هامش الربح تلقائياً
        $data['profit_margin'] = Product::calcProfitMargin(
            $data['purchase_price'] ?? 0,
            $data['sale_price'] ?? 0
        );

        $data['created_by'] = auth()->id();

        $product = Product::create($data);

        // إضافة مخزون ابتدائي إن وجد
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

        return redirect()->route('products.show', $product)
            ->with('success', 'تم إضافة المنتج بنجاح.');
    }

    // ─── نموذج تعديل منتج ────────────────────────────────────────────

    public function edit(Product $product): View
    {
        $categories = Category::active()->orderBy('name_ar')->get();
        $product->load('category');

        return view('products.edit', compact('product', 'categories'));
    }

    // ─── تحديث منتج ──────────────────────────────────────────────────

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        // رفع صورة جديدة
        if ($request->hasFile('image')) {
            if ($product->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // إعادة حساب هامش الربح
        $data['profit_margin'] = Product::calcProfitMargin(
            $data['purchase_price'] ?? $product->purchase_price,
            $data['sale_price'] ?? $product->sale_price
        );

        $old = $product->only(['name_ar', 'sale_price', 'purchase_price', 'quantity']);
        $product->update($data);

        ActivityLog::record('updated', "تعديل منتج: {$product->name_ar}", $product, $old);

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

        return redirect()->route('products.index')
            ->with('success', 'تم حذف المنتج بنجاح.');
    }

    // ─── البحث المباشر (Live Search) ─────────────────────────────────

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $products = Product::with('category')
            ->where(function ($w) use ($q) {
                $w->where('name_ar', 'like', "%{$q}%")
                  ->orWhere('name_en', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%")
                  ->orWhere('barcode', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'name_ar'      => $p->name_ar,
                'name_en'      => $p->name_en,
                'sku'          => $p->sku,
                'image'        => $p->image_url,
                'sale_price'   => number_format($p->sale_price, 2),
                'quantity'     => $p->quantity,
                'unit'         => $p->unit,
                'category'     => $p->category?->name_ar,
            ]);

        return response()->json(['results' => $products]);
    }

    // ─── تسوية المخزون ───────────────────────────────────────────────

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'new_quantity'  => 'required|integer|min:0',
            'warehouse_id'  => 'required|exists:warehouses,id',
            'reason'        => 'required|string|max:255',
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
}
