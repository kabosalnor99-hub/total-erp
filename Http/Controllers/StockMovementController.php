<?php

// المسار الكامل: app/Http/Controllers/StockMovementController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    // ─── قائمة حركات المخزون ─────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = StockMovement::with('product', 'warehouse', 'warehouseTo', 'user')
            ->latest();

        // فلتر المنتج
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // فلتر المستودع
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // فلتر النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فلتر التاريخ
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // فلتر البحث بالمنتج
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('product', fn($q) =>
                $q->where('name_ar', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%")
            );
        }

        $movements  = $query->paginate(25)->withQueryString();
        $products   = Product::active()->orderBy('name_ar')->get(['id', 'name_ar', 'sku']);
        $warehouses = Warehouse::active()->orderBy('name')->get(['id', 'name']);

        // إحصائيات سريعة لليوم
        $todayIn  = StockMovement::whereIn('type', ['in', 'return_in'])
                        ->whereDate('created_at', today())->sum('quantity');
        $todayOut = StockMovement::whereIn('type', ['out', 'return_out'])
                        ->whereDate('created_at', today())->sum('quantity');

        return view('stock-movements.index', compact(
            'movements', 'products', 'warehouses', 'todayIn', 'todayOut'
        ));
    }

    // ─── نموذج إضافة حركة يدوية ──────────────────────────────────────

    public function create(): View
    {
        $products   = Product::active()->orderBy('name_ar')->get(['id', 'name_ar', 'sku', 'quantity', 'unit']);
        $warehouses = Warehouse::active()->orderBy('name')->get(['id', 'name']);

        return view('stock-movements.create', compact('products', 'warehouses'));
    }

    // ─── حفظ حركة يدوية ──────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id'      => 'required|exists:products,id',
            'warehouse_id'    => 'required|exists:warehouses,id',
            'type'            => 'required|in:in,out,adjust,transfer',
            'quantity'        => 'required|integer|min:1',
            'warehouse_to_id' => 'required_if:type,transfer|nullable|exists:warehouses,id|different:warehouse_id',
            'reason'          => 'nullable|string|max:255',
            'notes'           => 'nullable|string|max:500',
            'unit_cost'       => 'nullable|numeric|min:0',
        ], [
            'product_id.required'      => 'المنتج مطلوب',
            'warehouse_id.required'    => 'المستودع مطلوب',
            'type.required'            => 'نوع الحركة مطلوب',
            'quantity.required'        => 'الكمية مطلوبة',
            'quantity.min'             => 'الكمية يجب أن تكون أكبر من صفر',
            'warehouse_to_id.required_if' => 'مستودع الوجهة مطلوب عند التحويل',
            'warehouse_to_id.different'   => 'مستودع الوجهة يجب أن يختلف عن المستودع المصدر',
        ]);

        $product = Product::findOrFail($data['product_id']);

        // التحقق من توفر الكمية عند الإخراج
        if (in_array($data['type'], ['out', 'return_out', 'transfer'])) {
            if ($product->quantity < $data['quantity']) {
                return back()
                    ->withInput()
                    ->with('error', "الكمية المطلوبة ({$data['quantity']}) تتجاوز المخزون المتاح ({$product->quantity}).");
            }
        }

        // تسجيل الحركة الرئيسية
        $movement = StockMovement::record(
            $data['product_id'],
            $data['warehouse_id'],
            $data['type'],
            $data['quantity'],
            [
                'warehouse_to_id' => $data['warehouse_to_id'] ?? null,
                'reason'          => $data['reason'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'unit_cost'       => $data['unit_cost'] ?? null,
            ]
        );

        // عند التحويل: تسجيل حركة واردة في المستودع الوجهة
        if ($data['type'] === 'transfer' && !empty($data['warehouse_to_id'])) {
            StockMovement::create([
                'product_id'      => $data['product_id'],
                'warehouse_id'    => $data['warehouse_to_id'],
                'type'            => 'in',
                'quantity'        => $data['quantity'],
                'quantity_before' => $product->quantity - $data['quantity'],
                'quantity_after'  => $product->quantity,
                'reference_type'  => StockMovement::class,
                'reference_id'    => $movement->id,
                'user_id'         => auth()->id(),
                'notes'           => 'تحويل وارد من مستودع: ' . Warehouse::find($data['warehouse_id'])?->name,
            ]);
        }

        ActivityLog::record(
            'stock_movement',
            "حركة مخزون ({$movement->type_label}): {$product->name_ar} — {$data['quantity']} {$product->unit}",
            $movement
        );

        return redirect()->route('stock-movements.index')
            ->with('success', 'تم تسجيل حركة المخزون بنجاح.');
    }

    // ─── عرض تفاصيل حركة ────────────────────────────────────────────

    public function show(StockMovement $stockMovement): View
    {
        $stockMovement->load('product', 'warehouse', 'warehouseTo', 'user');

        return view('stock-movements.show', compact('stockMovement'));
    }

    // ─── تقرير المخزون الحرج ─────────────────────────────────────────

    public function critical(): View
    {
        $products = Product::with('category')
            ->active()
            ->critical()
            ->orderBy('quantity')
            ->get();

        $outOfStock = Product::with('category')
            ->active()
            ->outOfStock()
            ->orderBy('name_ar')
            ->get();

        return view('stock-movements.critical', compact('products', 'outOfStock'));
    }

    // ─── تقرير المنتجات الراكدة ──────────────────────────────────────

    public function stagnant(): View
    {
        $products = Product::with('category')
            ->active()
            ->stagnant()
            ->where('quantity', '>', 0)
            ->orderBy('name_ar')
            ->get();

        return view('stock-movements.stagnant', compact('products'));
    }
}
