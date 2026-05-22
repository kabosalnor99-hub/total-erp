<?php

// المسار الكامل: app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    /** قائمة المدفوعات */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'customer', 'user'])->latest('payment_date');

        if ($search = $request->search) {
            $query->where('payment_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($method = $request->method) {
            $query->where('method', $method);
        }

        if ($from = $request->from) {
            $query->whereDate('payment_date', '>=', $from);
        }

        if ($to = $request->to) {
            $query->whereDate('payment_date', '<=', $to);
        }

        $payments = $query->paginate(15)->withQueryString();

        $stats = [
            'today'  => Payment::today()->sum('amount'),
            'month'  => Payment::thisMonth()->sum('amount'),
            'cash'   => Payment::thisMonth()->where('method', 'cash')->sum('amount'),
            'bank'   => Payment::thisMonth()->where('method', 'bank')->sum('amount'),
        ];

        return view('payments.index', compact('payments', 'stats'));
    }

    /** نموذج إضافة دفعة على فاتورة */
    public function create(Request $request)
    {
        $invoice = null;
        if ($request->invoice_id) {
            $invoice = Invoice::with('customer')->findOrFail($request->invoice_id);
        }

        return view('payments.create', compact('invoice'));
    }

    /** حفظ دفعة */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id'   => 'required|exists:invoices,id',
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'required|in:cash,bank,cheque,other',
            'reference'    => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        // التحقق أن المبلغ لا يتجاوز المتبقي
        if ($validated['amount'] > $invoice->remaining_amount + 0.01) {
            return back()->withInput()
                ->with('error', "المبلغ المدخل ({$validated['amount']}) أكبر من المتبقي ({$invoice->remaining_amount}).");
        }

        $payment = $this->invoiceService->addPayment($invoice, $validated);

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'create',
            'model_type' => Payment::class,
            'model_id'   => $payment->id,
            'details'    => "تسجيل دفعة {$payment->amount} لفاتورة {$invoice->invoice_number}",
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "تم تسجيل الدفعة {$payment->payment_number} بنجاح.");
    }

    /** عرض سند قبض */
    public function show(Payment $payment)
    {
        $payment->load(['invoice', 'customer', 'user']);
        return view('payments.show', compact('payment'));
    }

    /** طباعة سند قبض */
    public function print(Payment $payment)
    {
        $payment->load(['invoice.items', 'customer', 'user']);
        $settings = \App\Models\Setting::pluck('value', 'key');

        return view('payments.print', compact('payment', 'settings'));
    }
}
