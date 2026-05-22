<?php

// المسار الكامل: app/Http/Controllers/AccountController.php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(protected AccountingService $accounting) {}

    /** قائمة دليل الحسابات */
    public function index(Request $request)
    {
        $query = Account::with('parent')->orderBy('code');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        if ($type = $request->type) {
            $query->where('type', $type);
        }

        if ($request->has('leaf_only')) {
            $query->where('is_leaf', true);
        }

        $accounts = $query->paginate(20)->withQueryString();

        $stats = [
            'total'      => Account::count(),
            'assets'     => Account::where('type', 'asset')->count(),
            'liabilities'=> Account::where('type', 'liability')->count(),
            'revenues'   => Account::where('type', 'revenue')->count(),
            'expenses'   => Account::where('type', 'expense')->count(),
        ];

        // شجرة الحسابات للعرض الهرمي
        $tree = Account::whereNull('parent_id')->with('children.children')->orderBy('code')->get();

        return view('accounts.index', compact('accounts', 'stats', 'tree'));
    }

    /** نموذج إنشاء حساب جديد */
    public function create()
    {
        $parents = Account::where('is_active', true)->orderBy('code')->get();
        return view('accounts.create', compact('parents'));
    }

    /** حفظ حساب جديد */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar'              => 'required|string|max:255',
            'name_en'              => 'nullable|string|max:255',
            'type'                 => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance'       => 'required|in:debit,credit',
            'parent_id'            => 'nullable|exists:accounts,id',
            'is_leaf'              => 'boolean',
            'description'          => 'nullable|string',
            'opening_balance'      => 'nullable|numeric|min:0',
            'opening_balance_type' => 'required|in:debit,credit',
        ]);

        // توليد الكود تلقائياً
        $validated['code']  = Account::generateCode($validated['type'], $validated['parent_id'] ?? null);
        $validated['level'] = $validated['parent_id']
            ? (Account::find($validated['parent_id'])->level + 1)
            : 1;

        $validated['is_leaf'] = $request->boolean('is_leaf', true);

        // إذا كان لديه أب → الأب ليس حساباً تفصيلياً
        if ($validated['parent_id']) {
            Account::where('id', $validated['parent_id'])->update(['is_leaf' => false]);
        }

        $account = Account::create($validated);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'create',
            'module'      => 'accounts',
            'description' => "إنشاء حساب جديد: {$account->code} — {$account->name_ar}",
            'model_type'  => Account::class,
            'model_id'    => $account->id,
        ]);

        return redirect()->route('accounts.index')
            ->with('success', "تم إنشاء الحساب [{$account->code}] بنجاح.");
    }

    /** تعديل حساب */
    public function edit(Account $account)
    {
        $parents = Account::where('is_active', true)
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();

        return view('accounts.edit', compact('account', 'parents'));
    }

    /** تحديث حساب */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name_ar'              => 'required|string|max:255',
            'name_en'              => 'nullable|string|max:255',
            'normal_balance'       => 'required|in:debit,credit',
            'description'          => 'nullable|string',
            'is_active'            => 'boolean',
            'opening_balance'      => 'nullable|numeric|min:0',
            'opening_balance_type' => 'required|in:debit,credit',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $account->update($validated);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'update',
            'module'      => 'accounts',
            'description' => "تعديل حساب: {$account->code} — {$account->name_ar}",
            'model_type'  => Account::class,
            'model_id'    => $account->id,
        ]);

        return redirect()->route('accounts.index')
            ->with('success', 'تم تحديث الحساب بنجاح.');
    }

    /** دفتر الأستاذ لحساب معين */
    public function ledger(Request $request, Account $account)
    {
        $fromDate = $request->from_date ?? now()->startOfYear()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        $ledger = $this->accounting->getLedger($account, $fromDate, $toDate);

        return view('accounts.ledger', compact('account', 'ledger', 'fromDate', 'toDate'));
    }

    /** حذف حساب (فقط إذا لم تكن له قيود) */
    public function destroy(Account $account)
    {
        if ($account->journalLines()->exists()) {
            return back()->with('error', 'لا يمكن حذف حساب له قيود محاسبية.');
        }

        if ($account->children()->exists()) {
            return back()->with('error', 'لا يمكن حذف حساب له حسابات فرعية.');
        }

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'delete',
            'module'      => 'accounts',
            'description' => "حذف حساب: {$account->code} — {$account->name_ar}",
            'model_type'  => Account::class,
            'model_id'    => $account->id,
        ]);

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'تم حذف الحساب بنجاح.');
    }

    /** AJAX — البحث عن حساب (للقيود والسندات) */
    public function search(Request $request)
    {
        $q = $request->q ?? '';

        $accounts = Account::where('is_leaf', true)
            ->where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name_ar', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%");
            })
            ->orderBy('code')
            ->limit(20)
            ->get(['id', 'code', 'name_ar', 'type', 'normal_balance']);

        return response()->json($accounts);
    }

    /** ميزان المراجعة */
    public function trialBalance(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfYear()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        $data = $this->accounting->getTrialBalance($fromDate, $toDate);

        if ($request->format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.reports.trial_balance', array_merge($data, [
                'from_date' => $fromDate,
                'to_date'   => $toDate,
            ]));
            return $pdf->download("trial-balance-{$toDate}.pdf");
        }

        return view('reports.trial_balance', array_merge($data, [
            'from_date' => $fromDate,
            'to_date'   => $toDate,
        ]));
    }

    /** قائمة الدخل */
    public function incomeStatement(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        $data = $this->accounting->getIncomeStatement($fromDate, $toDate);

        if ($request->format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.reports.income_statement', $data);
            return $pdf->download("income-statement-{$fromDate}-{$toDate}.pdf");
        }

        return view('reports.income_statement', $data);
    }

    /** الميزانية العمومية */
    public function balanceSheet(Request $request)
    {
        $toDate = $request->to_date ?? now()->toDateString();

        $data = $this->accounting->getBalanceSheet($toDate);

        if ($request->format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.reports.balance_sheet', $data);
            return $pdf->download("balance-sheet-{$toDate}.pdf");
        }

        return view('reports.balance_sheet', $data);
    }
}
