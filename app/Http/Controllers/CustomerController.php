<?php

// المسار الكامل: app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /** قائمة العملاء */
    public function index(Request $request)
    {
        $query = Customer::query()->withCount('invoices');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($classification = $request->classification) {
            $query->where('classification', $classification);
        }

        if ($type = $request->type) {
            $query->where('type', $type);
        }

        if ($request->has_balance) {
            $query->where('balance', '>', 0);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'        => Customer::count(),
            'vip'          => Customer::where('classification', 'vip')->count(),
            'with_balance' => Customer::where('balance', '>', 0)->count(),
            'total_debt'   => Customer::sum('balance'),
        ];

        return view('customers.index', compact('customers', 'stats'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'phone_alt'      => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'type'           => 'required|in:individual,company',
            'company_name'   => 'nullable|string|max:255',
            'tax_number'     => 'nullable|string|max:50',
            'classification' => 'required|in:vip,regular,inactive',
            'credit_limit'   => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $customer = Customer::create($validated);

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'create',
            'model_type' => Customer::class,
            'model_id'   => $customer->id,
            'details'    => "إضافة عميل: {$customer->name}",
        ]);

        if ($request->wantsJson()) {
            return response()->json(['customer' => $customer], 201);
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', 'تم إضافة العميل بنجاح.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'invoices' => fn($q) => $q->latest()->take(10),
            'payments' => fn($q) => $q->latest()->take(10),
        ]);

        $stats = [
            'total_invoiced' => $customer->invoices()->whereIn('status', ['confirmed','paid','partial'])->sum('total'),
            'total_paid'     => $customer->payments()->sum('amount'),
            'balance'        => $customer->balance,
            'invoices_count' => $customer->invoices()->count(),
        ];

        return view('customers.show', compact('customer', 'stats'));
    }

    /** كشف حساب العميل */
    public function statement(Customer $customer, Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        // الرصيد الافتتاحي (قبل فترة الكشف)
        $openingInvoiced = $customer->invoices()
            ->whereDate('created_at', '<', $from)
            ->whereIn('status', ['confirmed', 'paid', 'partial'])
            ->sum('total');

        $openingPaid = $customer->payments()
            ->whereDate('created_at', '<', $from)
            ->sum('amount');

        $openingBalance = $openingInvoiced - $openingPaid;

        // فواتير الفترة
        $periodInvoices = $customer->invoices()
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->whereIn('status', ['confirmed', 'paid', 'partial'])
            ->latest()->get();

        // دفعات الفترة
        $periodPayments = $customer->payments()
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->latest()->get();

        // بناء قائمة الحركات
        $transactions = collect();

        foreach ($periodInvoices as $inv) {
            $transactions->push([
                'id'        => $inv->id,
                'date'      => $inv->created_at,
                'type'      => 'invoice',
                'reference' => $inv->invoice_number,
                'amount'    => $inv->total,
                'notes'     => $inv->notes,
            ]);
        }

        foreach ($periodPayments as $pay) {
            $transactions->push([
                'id'        => $pay->id,
                'date'      => $pay->created_at,
                'type'      => 'payment',
                'reference' => $pay->payment_number ?? ('PMT-' . str_pad($pay->id, 5, '0', STR_PAD_LEFT)),
                'amount'    => $pay->amount,
                'notes'     => $pay->notes,
            ]);
        }

        $transactions = $transactions->sortBy('date')->values();

        $summary = [
            'opening_balance' => $openingBalance,
            'total_invoiced'  => $periodInvoices->sum('total'),
            'total_paid'      => $periodPayments->sum('amount'),
            'balance'         => $openingBalance + $periodInvoices->sum('total') - $periodPayments->sum('amount'),
        ];

        // الفواتير غير المسددة
        $unpaidInvoices = $customer->invoices()
            ->whereIn('status', ['confirmed', 'partial'])
            ->where('remaining_amount', '>', 0)
            ->orderBy('due_date')
            ->get();

        return view('customers.statement', compact(
            'customer', 'transactions', 'summary', 'unpaidInvoices', 'from', 'to'
        ));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'phone_alt'      => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'type'           => 'required|in:individual,company',
            'company_name'   => 'nullable|string|max:255',
            'tax_number'     => 'nullable|string|max:50',
            'classification' => 'required|in:vip,regular,inactive',
            'credit_limit'   => 'nullable|numeric|min:0',
            'is_active'      => 'boolean',
            'notes'          => 'nullable|string',
        ]);

        $customer->update($validated);

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'update',
            'model_type' => Customer::class,
            'model_id'   => $customer->id,
            'details'    => "تعديل عميل: {$customer->name}",
        ]);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'تم تحديث بيانات العميل.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->invoices()->whereIn('status', ['confirmed', 'partial'])->exists()) {
            return back()->with('error', 'لا يمكن حذف عميل لديه فواتير مفتوحة.');
        }

        $name = $customer->name;
        $customer->delete();

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'delete',
            'model_type' => Customer::class,
            'model_id'   => $customer->id,
            'details'    => "حذف عميل: {$name}",
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'تم حذف العميل.');
    }

    public function search(Request $request)
    {
        $customers = Customer::where('is_active', true)
            ->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('phone', 'like', "%{$request->q}%");
            })
            ->select('id', 'name', 'phone', 'balance', 'credit_limit', 'classification')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }
}
