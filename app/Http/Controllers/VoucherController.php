<?php

// المسار الكامل: app/Http/Controllers/VoucherController.php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\Voucher;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function __construct(protected AccountingService $accounting) {}

    /** قائمة السندات */
    public function index(Request $request)
    {
        $query = Voucher::with(['account', 'cashAccount', 'user'])->latest('date');

        if ($type = $request->type) {
            $query->where('type', $type);
        }

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($from = $request->from_date) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to = $request->to_date) {
            $query->whereDate('date', '<=', $to);
        }

        $vouchers = $query->paginate(15)->withQueryString();

        $stats = [
            'total_receipts' => Voucher::where('type', 'receipt')->sum('amount'),
            'total_payments' => Voucher::where('type', 'payment')->sum('amount'),
            'count_receipts' => Voucher::where('type', 'receipt')->count(),
            'count_payments' => Voucher::where('type', 'payment')->count(),
        ];

        return view('vouchers.index', compact('vouchers', 'stats'));
    }

    /** نموذج إنشاء سند */
    public function create(Request $request)
    {
        $type = $request->type ?? 'receipt'; // receipt | payment

        $accounts = Account::where('is_leaf', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar', 'type']);

        // حسابات الصندوق/البنك (أصول سائلة)
        $cashAccounts = Account::where('type', 'asset')
            ->where('is_leaf', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar']);

        return view('vouchers.create', compact('type', 'accounts', 'cashAccounts'));
    }

    /** حفظ سند جديد */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'            => 'required|in:receipt,payment',
            'date'            => 'required|date',
            'account_id'      => 'required|exists:accounts,id',
            'cash_account_id' => 'required|exists:accounts,id',
            'amount'          => 'required|numeric|min:0.01',
            'description'     => 'required|string|max:500',
            'payment_method'  => 'required|in:cash,bank,cheque',
            'cheque_number'   => 'nullable|string|max:50',
            'bank_reference'  => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
        ]);

        $validated['voucher_number'] = Voucher::generateNumber($validated['type']);
        $validated['user_id']        = auth()->id();

        $voucher = Voucher::create($validated);

        // إنشاء القيد المحاسبي التلقائي
        try {
            $this->accounting->createVoucherEntry($voucher);
        } catch (\Exception $e) {
            // تسجيل الخطأ ولكن إكمال حفظ السند
            logger()->warning("فشل إنشاء قيد للسند {$voucher->voucher_number}: " . $e->getMessage());
        }

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'create',
            'module'      => 'vouchers',
            'description' => "إنشاء {$voucher->type_label} #{$voucher->voucher_number} — {$voucher->amount}",
            'model_type'  => Voucher::class,
            'model_id'    => $voucher->id,
        ]);

        return redirect()->route('vouchers.show', $voucher)
            ->with('success', "تم إنشاء {$voucher->type_label} #{$voucher->voucher_number} بنجاح.");
    }

    /** عرض سند */
    public function show(Voucher $voucher)
    {
        $voucher->load(['account', 'cashAccount', 'journalEntry.lines.account', 'user']);
        return view('vouchers.show', compact('voucher'));
    }

    /** طباعة سند */
    public function print(Voucher $voucher)
    {
        $voucher->load(['account', 'cashAccount', 'user']);
        return view('vouchers.print', compact('voucher'));
    }

    /** حذف سند (فقط إذا لم يكن له قيد مُرحَّل) */
    public function destroy(Voucher $voucher)
    {
        if ($voucher->journalEntry && $voucher->journalEntry->status === 'posted') {
            return back()->with('error', 'لا يمكن حذف سند له قيد مُرحَّل.');
        }

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'delete',
            'module'      => 'vouchers',
            'description' => "حذف {$voucher->type_label} #{$voucher->voucher_number}",
            'model_type'  => Voucher::class,
            'model_id'    => $voucher->id,
        ]);

        // حذف القيد المرتبط إن وجد وكان مسودة
        if ($voucher->journalEntry && $voucher->journalEntry->status === 'draft') {
            $voucher->journalEntry->lines()->delete();
            $voucher->journalEntry->delete();
        }

        $voucher->delete();

        return redirect()->route('vouchers.index')
            ->with('success', 'تم حذف السند بنجاح.');
    }
}
