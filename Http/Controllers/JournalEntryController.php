<?php

// المسار الكامل: app/Http/Controllers/JournalEntryController.php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    /** قائمة القيود المحاسبية */
    public function index(Request $request)
    {
        $query = JournalEntry::with('user')->latest('date');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($source = $request->source) {
            $query->where('source', $source);
        }

        if ($from = $request->from_date) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to = $request->to_date) {
            $query->whereDate('date', '<=', $to);
        }

        $entries = $query->paginate(15)->withQueryString();

        $stats = [
            'total'  => JournalEntry::count(),
            'draft'  => JournalEntry::where('status', 'draft')->count(),
            'posted' => JournalEntry::where('status', 'posted')->count(),
        ];

        return view('journal.index', compact('entries', 'stats'));
    }

    /** نموذج إنشاء قيد يدوي */
    public function create()
    {
        $accounts = Account::where('is_leaf', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name_ar', 'type', 'normal_balance']);

        return view('journal.create', compact('accounts'));
    }

    /** حفظ قيد جديد */
    public function store(Request $request)
    {
        $request->validate([
            'date'              => 'required|date',
            'description'       => 'required|string|max:500',
            'notes'             => 'nullable|string',
            'lines'             => 'required|array|min:2',
            'lines.*.account_id'=> 'required|exists:accounts,id',
            'lines.*.debit'     => 'required|numeric|min:0',
            'lines.*.credit'    => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        // التحقق من التوازن
        $totalDebit  = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()
                ->withInput()
                ->with('error', "القيد غير متوازن. المدين: {$totalDebit} — الدائن: {$totalCredit}");
        }

        // التحقق أن كل سطر له مدين أو دائن وليس كلاهما
        foreach ($request->lines as $i => $line) {
            if ($line['debit'] > 0 && $line['credit'] > 0) {
                return back()
                    ->withInput()
                    ->with('error', "السطر " . ($i + 1) . " لا يمكن أن يحتوي على مدين ودائن في نفس الوقت.");
            }
        }

        DB::transaction(function () use ($request) {

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'date'         => $request->date,
                'description'  => $request->description,
                'user_id'      => auth()->id(),
                'status'       => 'draft',
                'source'       => 'manual',
                'notes'        => $request->notes,
            ]);

            foreach ($request->lines as $i => $line) {
                if ($line['debit'] == 0 && $line['credit'] == 0) continue;

                JournalEntryLine::create([
                    'entry_id'    => $entry->id,
                    'account_id'  => $line['account_id'],
                    'debit'       => $line['debit'] ?? 0,
                    'credit'      => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? null,
                    'sort_order'  => $i + 1,
                ]);
            }

            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'create',
                'module'      => 'journal',
                'description' => "إنشاء قيد: {$entry->entry_number}",
                'model_type'  => JournalEntry::class,
                'model_id'    => $entry->id,
            ]);
        });

        return redirect()->route('journal.index')
            ->with('success', 'تم إنشاء القيد كمسودة. يمكنك ترحيله عند المراجعة.');
    }

    /** عرض قيد */
    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load(['lines.account', 'user']);
        return view('journal.show', compact('journalEntry'));
    }

    /** ترحيل قيد (من مسودة → مُرحَّل) */
    public function post(JournalEntry $journalEntry)
    {
        try {
            $journalEntry->load('lines');
            $journalEntry->post();

            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'post',
                'module'      => 'journal',
                'description' => "ترحيل قيد: {$journalEntry->entry_number}",
                'model_type'  => JournalEntry::class,
                'model_id'    => $journalEntry->id,
            ]);

            return back()->with('success', "تم ترحيل القيد {$journalEntry->entry_number} بنجاح.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** إلغاء ترحيل قيد */
    public function unpost(JournalEntry $journalEntry)
    {
        // فقط المدير المالي أو مدير النظام
        if (! auth()->user()->hasRole(['admin', 'finance_manager'])) {
            return back()->with('error', 'ليس لديك صلاحية إلغاء ترحيل القيود.');
        }

        // لا يمكن إلغاء ترحيل قيد مرتبط بسند مطبوع
        if ($journalEntry->vouchers()->exists()) {
            return back()->with('error', 'لا يمكن إلغاء ترحيل قيد مرتبط بسند.');
        }

        try {
            $journalEntry->unpost();

            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'unpost',
                'module'      => 'journal',
                'description' => "إلغاء ترحيل قيد: {$journalEntry->entry_number}",
                'model_type'  => JournalEntry::class,
                'model_id'    => $journalEntry->id,
            ]);

            return back()->with('success', "تم إلغاء ترحيل القيد {$journalEntry->entry_number}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** حذف قيد (مسودة فقط) */
    public function destroy(JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'posted') {
            return back()->with('error', 'لا يمكن حذف قيد مُرحَّل.');
        }

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'delete',
            'module'      => 'journal',
            'description' => "حذف قيد: {$journalEntry->entry_number}",
            'model_type'  => JournalEntry::class,
            'model_id'    => $journalEntry->id,
        ]);

        $journalEntry->lines()->delete();
        $journalEntry->delete();

        return redirect()->route('journal.index')
            ->with('success', 'تم حذف القيد بنجاح.');
    }
}
