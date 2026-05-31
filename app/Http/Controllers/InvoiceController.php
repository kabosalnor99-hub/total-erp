<?php

// المسار الكامل: app/Http/Controllers/InvoiceController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\CacheService;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    /** قائمة الفواتير */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'user'])->latest();

        if ($search = $request->search) {
            $query->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($type = $request->type) {
            $query->where('type', $type);
        }

        if ($from = $request->from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $invoices = $query->paginate(15)->withQueryString();

        $stats = [
            'today_total'   => Invoice::today()->whereNotIn('status',['draft','cancelled'])->sum('total'),
            'month_total'   => Invoice::thisMonth()->whereNotIn('status',['draft','cancelled'])->sum('total'),
            'pending'       => Invoice::whereIn('status', ['confirmed','partial'])->count(),
            'overdue'       => Invoice::overdue()->count(),
        ];

        return view('invoices.index', compact('invoices', 'stats'));
    }

    /** نموذج إنشاء فاتورة */
    public function create()
    {
        $customers  = Customer::active()->orderBy('name')->get(['id','name','phone','balance','credit_limit']);
        $products   = Product::active()->orderBy('name_ar')->get(['id','name_ar','sku','sale_price','quantity','unit']);
        $warehouses = Warehouse::orderBy('name')->get(['id','name']);

        return view('invoices.create', compact('customers', 'products', 'warehouses'));
    }

    /** حفظ فاتورة جديدة */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'      => 'nullable|exists:customers,id',
            'type'             => 'required|in:cash,credit,partial',
            'warehouse_id'     => 'required|exists:warehouses,id',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount'  => 'nullable|numeric|min:0',
            'tax_percent'      => 'nullable|numeric|min:0|max:100',
            'due_date'         => 'nullable|date|after_or_equal:today',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $invoice = $this->invoiceService->createInvoice(
                $request->except('items'),
                $request->items
            );

            ActivityLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'create',
                'model_type' => Invoice::class,
                'model_id'   => $invoice->id,
                'details'    => "إنشاء فاتورة: {$invoice->invoice_number}",
            ]);

            CacheService::forgetDashboard();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "تم إنشاء الفاتورة {$invoice->invoice_number} بنجاح.");

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /** عرض فاتورة */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'user', 'items.product', 'payments.user']);

        return view('invoices.show', compact('invoice'));
    }

    /** طباعة فاتورة (HTML) */
    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'user', 'items.product', 'payments']);
        $settings = \App\Models\Setting::pluck('value', 'key');

        return view('invoices.print', compact('invoice', 'settings'));
    }

    /** تصدير PDF */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'user', 'items.product', 'payments']);
        $settings = \App\Models\Setting::pluck('value', 'key');

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'settings'))
            ->setPaper('a4')
            ->setOptions([
                'defaultFont'             => 'noto naskh arabic',
                'isHtml5ParserEnabled'    => true,
                'isRemoteEnabled'         => true,
                'isFontSubsettingEnabled' => true,
                'dpi'                     => 110,
            ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /** إلغاء فاتورة */
    public function cancel(Invoice $invoice)
    {
        try {
            $this->invoiceService->cancelInvoice($invoice);

            ActivityLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'cancel',
                'model_type' => Invoice::class,
                'model_id'   => $invoice->id,
                'details'    => "إلغاء فاتورة: {$invoice->invoice_number}",
            ]);

            CacheService::forgetDashboard();

            return back()->with('success', 'تم إلغاء الفاتورة.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** تقرير المبيعات */
    public function report(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $invoices = Invoice::with(['customer', 'user'])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->latest()
            ->get();

        $summary = [
            'total_sales'  => $invoices->sum('total'),
            'total_paid'   => $invoices->sum('paid_amount'),
            'total_due'    => $invoices->sum('remaining_amount'),
            'count'        => $invoices->count(),
            'cash_sales'   => $invoices->where('type', 'cash')->sum('total'),
            'credit_sales' => $invoices->where('type', 'credit')->sum('total'),
        ];

        return view('invoices.report', compact('invoices', 'summary', 'from', 'to'));
    }

    /** تقرير المستحقات المتأخرة */
    public function aging(Request $request)
    {
        $overdue = Invoice::overdue()
            ->with('customer')
            ->latest('due_date')
            ->get();

        return view('invoices.aging', compact('overdue'));
    }
}
