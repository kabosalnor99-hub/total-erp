<?php

// المسار الكامل: app/Http/Controllers/PurchaseRequestController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = PurchaseRequest::with(['user', 'approver'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('request_number', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('purchase-requests.index', compact('requests'));
    }

    public function create()
    {
        $products = Product::active()->orderBy('name_ar')->get();
        return view('purchase-requests.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'needed_by'              => 'nullable|date|after:today',
            'notes'                  => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.estimated_price'=> 'nullable|numeric|min:0',
            'items.*.notes'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $pr = PurchaseRequest::create([
                'request_number' => PurchaseRequest::generateNumber(),
                'user_id'        => auth()->id(),
                'status'         => 'pending',
                'needed_by'      => $data['needed_by'] ?? null,
                'notes'          => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'product_id'          => $item['product_id'],
                    'quantity'            => $item['quantity'],
                    'estimated_price'     => $item['estimated_price'] ?? 0,
                    'notes'               => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('purchase-requests.index')
            ->with('success', 'تم إرسال طلب الشراء بنجاح');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['user', 'approver', 'items.product', 'purchaseOrder']);
        return view('purchase-requests.show', compact('purchaseRequest'));
    }

    /** اعتماد طلب الشراء */
    public function approve(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return back()->with('error', 'هذا الطلب لا يمكن اعتماده');
        }

        $purchaseRequest->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'تم اعتماد طلب الشراء');
    }

    /** رفض طلب الشراء */
    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        if ($purchaseRequest->status !== 'pending') {
            return back()->with('error', 'هذا الطلب لا يمكن رفضه');
        }

        $purchaseRequest->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'تم رفض طلب الشراء');
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return back()->with('error', 'لا يمكن حذف طلب غير معلق');
        }

        $purchaseRequest->delete();

        return redirect()->route('purchase-requests.index')
            ->with('success', 'تم حذف طلب الشراء');
    }
}
