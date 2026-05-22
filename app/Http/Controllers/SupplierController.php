<?php

// المسار الكامل: app/Http/Controllers/SupplierController.php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(protected PurchaseService $purchaseService) {}

    public function index(Request $request)
    {
        $suppliers = Supplier::query()
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->rating, fn($q) => $q->where('rating', $request->rating))
            ->withCount('purchaseOrders')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'company_name'  => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string',
            'tax_number'    => 'nullable|string|max:50',
            'payment_terms' => 'required|in:cash,net_7,net_15,net_30,net_60',
            'rating'        => 'required|integer|between:1,5',
            'notes'         => 'nullable|string',
        ]);

        $supplier = Supplier::create($data);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'تم إضافة المورد بنجاح');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchaseOrders' => fn($q) => $q->latest()->limit(10)]);

        $stats = [
            'total_purchases' => $supplier->totalPurchases(),
            'total_paid'      => $supplier->totalPaid(),
            'outstanding'     => $supplier->outstandingBalance(),
            'orders_count'    => $supplier->purchaseOrders()->count(),
        ];

        return view('suppliers.show', compact('supplier', 'stats'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'company_name'  => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string',
            'tax_number'    => 'nullable|string|max:50',
            'payment_terms' => 'required|in:cash,net_7,net_15,net_30,net_60',
            'rating'        => 'required|integer|between:1,5',
            'status'        => 'required|in:active,inactive',
            'notes'         => 'nullable|string',
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'تم تحديث بيانات المورد');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'لا يمكن حذف مورد لديه أوامر شراء');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'تم حذف المورد');
    }

    /** كشف حساب المورد */
    public function statement(Request $request, Supplier $supplier)
    {
        $from = $request->from_date ?? now()->startOfMonth()->toDateString();
        $to   = $request->to_date   ?? now()->toDateString();

        $orders = $supplier->purchaseOrders()
            ->with('items.product')
            ->whereIn('status', ['received', 'partial'])
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        $payments = $supplier->payments()
            ->whereBetween('payment_date', [$from, $to])
            ->latest('payment_date')
            ->get();

        $openingBalance = $supplier->purchaseOrders()
            ->whereIn('status', ['received', 'partial'])
            ->where('created_at', '<', $from)
            ->sum('total')
            - $supplier->payments()
            ->where('payment_date', '<', $from)
            ->sum('amount');

        return view('suppliers.statement', compact(
            'supplier', 'orders', 'payments', 'from', 'to', 'openingBalance'
        ));
    }

    /** بحث سريع للـ AJAX */
    public function search(Request $request)
    {
        $suppliers = Supplier::active()
            ->search($request->q ?? '')
            ->select('id', 'name', 'company_name', 'phone', 'balance', 'payment_terms')
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id'            => $s->id,
                'name'          => $s->name,
                'company_name'  => $s->company_name,
                'phone'         => $s->phone,
                'balance'       => number_format($s->balance, 2),
                'payment_terms' => $s->payment_terms_label,
            ]);

        return response()->json($suppliers);
    }

    /** تسجيل دفعة للمورد */
    public function pay(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'amount'            => 'required|numeric|min:0.01',
            'method'            => 'required|in:cash,bank_transfer,check,other',
            'reference'         => 'nullable|string|max:100',
            'payment_date'      => 'required|date',
            'notes'             => 'nullable|string',
        ]);

        $data['supplier_id'] = $supplier->id;

        $this->purchaseService->paySupplier($data);

        return back()->with('success', 'تم تسجيل الدفعة بنجاح');
    }
}
