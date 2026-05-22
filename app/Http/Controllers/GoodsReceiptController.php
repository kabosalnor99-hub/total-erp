<?php

// المسار الكامل: app/Http/Controllers/GoodsReceiptController.php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    public function __construct(protected PurchaseService $purchaseService) {}

    public function index(Request $request)
    {
        $receipts = GoodsReceipt::with(['purchaseOrder.supplier', 'warehouse', 'user'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('receipt_number', 'like', "%{$request->search}%"))
            ->when($request->from_date, fn($q) => $q->whereDate('received_date', '>=', $request->from_date))
            ->when($request->to_date,   fn($q) => $q->whereDate('received_date', '<=', $request->to_date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('goods-receipts.index', compact('receipts'));
    }

    /** نموذج استلام بضاعة — مرتبط بأمر شراء محدد */
    public function create(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
        ]);

        $order = PurchaseOrder::with(['supplier', 'items.product'])
            ->findOrFail($request->purchase_order_id);

        if (!in_array($order->status, ['sent', 'partial'])) {
            return redirect()->route('purchase-orders.show', $order)
                ->with('error', 'لا يمكن استلام بضاعة لهذا الأمر في حالته الحالية');
        }

        $warehouses = Warehouse::orderBy('name')->get();

        // بنود لم تُستلم بعد أو استُلمت جزئياً
        $pendingItems = $order->items->filter(
            fn($i) => $i->received_quantity < $i->quantity
        );

        return view('goods-receipts.create', compact('order', 'warehouses', 'pendingItems'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id'            => 'required|exists:purchase_orders,id',
            'warehouse_id'                 => 'required|exists:warehouses,id',
            'received_date'                => 'required|date',
            'notes'                        => 'nullable|string',
            'items'                        => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.product_id'           => 'required|exists:products,id',
            'items.*.quantity_received'    => 'required|numeric|min:0.01',
            'items.*.unit_price'           => 'required|numeric|min:0',
            'items.*.notes'                => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $receipt = GoodsReceipt::create([
                'receipt_number'    => GoodsReceipt::generateNumber(),
                'purchase_order_id' => $data['purchase_order_id'],
                'warehouse_id'      => $data['warehouse_id'],
                'user_id'           => auth()->id(),
                'status'            => 'draft',
                'received_date'     => $data['received_date'],
                'notes'             => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id'       => $receipt->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'product_id'             => $item['product_id'],
                    'quantity_received'      => $item['quantity_received'],
                    'unit_price'             => $item['unit_price'],
                    'notes'                  => $item['notes'] ?? null,
                ]);
            }

            return $receipt;
        });

        return redirect()->route('goods-receipts.index')
            ->with('success', 'تم إنشاء وصل الاستلام — قم بتأكيده لتحديث المخزون');
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load([
            'purchaseOrder.supplier',
            'items.product',
            'items.purchaseOrderItem',
            'warehouse',
            'user',
        ]);

        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    /** تأكيد الاستلام → تحديث المخزون + قيد محاسبي */
    public function confirm(GoodsReceipt $goodsReceipt)
    {
        try {
            $this->purchaseService->confirmReceipt($goodsReceipt);
            return redirect()->route('goods-receipts.show', $goodsReceipt)
                ->with('success', 'تم تأكيد الاستلام وتحديث المخزون بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->status === 'confirmed') {
            return back()->with('error', 'لا يمكن حذف وصل استلام مؤكد');
        }

        $goodsReceipt->delete();

        return redirect()->route('goods-receipts.index')
            ->with('success', 'تم حذف وصل الاستلام');
    }
}
