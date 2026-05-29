<?php

// المسار الكامل: app/Http/Controllers/PurchaseOrderController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(protected PurchaseService $purchaseService) {}

    public function index(Request $request)
    {
        $orders = PurchaseOrder::with(['supplier', 'user'])
            ->when($request->search,   fn($q) => $q->search($request->search))
            ->when($request->status,   fn($q) => $q->where('status', $request->status))
            ->when($request->supplier_id, fn($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->from_date, fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date,   fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('purchase-orders.index', compact('orders', 'suppliers'));
    }

    public function create(Request $request)
    {
        $suppliers        = Supplier::active()->orderBy('name')->get();
        $products         = Product::active()->orderBy('name_ar')->get();
        $purchaseRequests = PurchaseRequest::where('status', 'approved')->latest()->get();

        // تجهيز بيانات المنتجات كـ JSON جاهز للـ View
        $productsJson = $products->map(fn($p) => [
            'id'             => $p->id,
            'purchase_price' => $p->purchase_price,
        ])->values()->toJson();

        // تحميل بنود طلب الشراء إذا مرر
        $fromRequest     = null;
        $fromRequestJson = 'null';

        if ($request->from_request) {
            $fromRequest = PurchaseRequest::with('items.product')
                ->findOrFail($request->from_request);

            $fromRequestJson = $fromRequest->items->map(fn($i) => [
                'product_id' => $i->product_id,
                'quantity'   => $i->quantity,
                'unit_price' => $i->estimated_price ?? 0,
                'discount'   => 0,
                'total'      => $i->quantity * ($i->estimated_price ?? 0),
            ])->values()->toJson();
        }

        return view('purchase-orders.create', compact(
            'suppliers', 'products', 'purchaseRequests',
            'fromRequest', 'productsJson', 'fromRequestJson'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'           => 'required|exists:suppliers,id',
            'purchase_request_id'   => 'nullable|exists:purchase_requests,id',
            'expected_date'         => 'nullable|date',
            'discount'              => 'nullable|numeric|min:0',
            'tax_rate'              => 'nullable|numeric|min:0|max:100',
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.discount'      => 'nullable|numeric|min:0',
            'items.*.notes'         => 'nullable|string',
        ]);

        $order = $this->purchaseService->createOrder($data, $data['items']);

        return redirect()->route('purchase-orders.show', $order)
            ->with('success', 'تم إنشاء أمر الشراء بنجاح');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'user',
            'items.product',
            'goodsReceipts.items.product',
            'payments',
            'purchaseRequest',
        ]);

        $warehouses = Warehouse::orderBy('name')->get();

        return view('purchase-orders.show', compact('purchaseOrder', 'warehouses'));
    }

    /** تغيير حالة الأمر إلى "أُرسل للمورد" */
    public function markSent(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'لا يمكن تغيير حالة هذا الأمر');
        }

        $purchaseOrder->update(['status' => 'sent']);

        return back()->with('success', 'تم تحديث حالة الأمر إلى "أُرسل للمورد"');
    }

    /** إلغاء أمر الشراء */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft', 'sent'])) {
            return back()->with('error', 'لا يمكن إلغاء هذا الأمر');
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return back()->with('success', 'تم إلغاء أمر الشراء');
    }

    /** طباعة أمر الشراء PDF */
    public function pdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product', 'user']);

        $pdf = Pdf::loadView('pdf.purchase_order', compact('purchaseOrder'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled'         => true,
                'isHtml5ParserEnabled'    => true,
                'isFontSubsettingEnabled' => true,
                'defaultMediaType'        => 'print',
                'defaultFont'             => 'cairo',
            ]);

        return $pdf->download("purchase-order-{$purchaseOrder->order_number}.pdf");
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'لا يمكن حذف إلا أوامر الشراء المسودة');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'تم حذف أمر الشراء');
    }
}
