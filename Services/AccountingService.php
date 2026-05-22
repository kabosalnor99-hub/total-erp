<?php

// المسار الكامل: app/Services/AccountingService.php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PosTransaction;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    // ─── ثوابت أكواد الحسابات الافتراضية ────────────────────────
    // يمكن تعديلها من إعدادات النظام لاحقاً
    const ACCOUNT_CASH          = '1001'; // الصندوق النقدي
    const ACCOUNT_RECEIVABLES   = '1101'; // ذمم مدينة (عملاء)
    const ACCOUNT_INVENTORY     = '1201'; // المخزون
    const ACCOUNT_SALES         = '4001'; // إيرادات المبيعات
    const ACCOUNT_COGS          = '5001'; // تكلفة البضاعة المباعة
    const ACCOUNT_PAYABLES      = '2001'; // ذمم دائنة (موردون)
    const ACCOUNT_PURCHASES     = '5101'; // المشتريات
    const ACCOUNT_SALARIES      = '5201'; // مصاريف الرواتب

    // ─── قيد فاتورة البيع ────────────────────────────────────────

    /**
     * قيد فاتورة بيع نقدية
     * من حـ/الصندوق → إلى حـ/المبيعات
     */
    public function createInvoiceEntry(Invoice $invoice): JournalEntry
    {
        return DB::transaction(function () use ($invoice) {

            $cashAccount        = $this->findAccount(self::ACCOUNT_CASH);
            $receivablesAccount = $this->findAccount(self::ACCOUNT_RECEIVABLES);
            $salesAccount       = $this->findAccount(self::ACCOUNT_SALES);

            $entry = JournalEntry::create([
                'entry_number'   => JournalEntry::generateNumber(),
                'date'           => $invoice->created_at->toDateString(),
                'description'    => "فاتورة بيع #{$invoice->invoice_number}",
                'user_id'        => auth()->id() ?? $invoice->user_id,
                'status'         => 'posted',
                'reference_type' => Invoice::class,
                'reference_id'   => $invoice->id,
                'source'         => 'invoice',
            ]);

            // الجانب المدين: الصندوق (نقدي) أو الذمم (آجل)
            $debitAccount = $invoice->type === 'cash' ? $cashAccount : $receivablesAccount;

            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $debitAccount->id,
                'debit'       => $invoice->total,
                'credit'      => 0,
                'description' => $invoice->type === 'cash' ? 'استلام نقدي' : 'ذمة عميل',
                'sort_order'  => 1,
            ]);

            // الجانب الدائن: المبيعات
            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $salesAccount->id,
                'debit'       => 0,
                'credit'      => $invoice->total,
                'description' => "مبيعات — {$invoice->invoice_number}",
                'sort_order'  => 2,
            ]);

            return $entry;
        });
    }

    /**
     * قيد تحصيل دفعة من عميل (للفواتير الآجلة)
     * من حـ/الصندوق → إلى حـ/الذمم المدينة
     */
    public function createPaymentEntry(Payment $payment): JournalEntry
    {
        return DB::transaction(function () use ($payment) {

            $cashAccount        = $this->findAccount(self::ACCOUNT_CASH);
            $receivablesAccount = $this->findAccount(self::ACCOUNT_RECEIVABLES);

            $entry = JournalEntry::create([
                'entry_number'   => JournalEntry::generateNumber(),
                'date'           => $payment->payment_date,
                'description'    => "تحصيل دفعة #{$payment->payment_number}",
                'user_id'        => auth()->id() ?? $payment->user_id,
                'status'         => 'posted',
                'reference_type' => Payment::class,
                'reference_id'   => $payment->id,
                'source'         => 'payment',
            ]);

            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $cashAccount->id,
                'debit'       => $payment->amount,
                'credit'      => 0,
                'description' => 'استلام نقدي من عميل',
                'sort_order'  => 1,
            ]);

            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $receivablesAccount->id,
                'debit'       => 0,
                'credit'      => $payment->amount,
                'description' => 'تسوية ذمة عميل',
                'sort_order'  => 2,
            ]);

            return $entry;
        });
    }

    /**
     * قيد مبيعات نقطة البيع (POS)
     * من حـ/الصندوق → إلى حـ/المبيعات
     */
    public function createPosEntry(PosTransaction $transaction): JournalEntry
    {
        return DB::transaction(function () use ($transaction) {

            $cashAccount  = $this->findAccount(self::ACCOUNT_CASH);
            $salesAccount = $this->findAccount(self::ACCOUNT_SALES);
            $receivables  = $this->findAccount(self::ACCOUNT_RECEIVABLES);

            $entry = JournalEntry::create([
                'entry_number'   => JournalEntry::generateNumber(),
                'date'           => $transaction->created_at->toDateString(),
                'description'    => "مبيعات POS #{$transaction->transaction_number}",
                'user_id'        => auth()->id() ?? $transaction->session->user_id,
                'status'         => 'posted',
                'reference_type' => PosTransaction::class,
                'reference_id'   => $transaction->id,
                'source'         => 'pos',
            ]);

            $debitAccount = $transaction->payment_type === 'cash' ? $cashAccount : $receivables;

            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $debitAccount->id,
                'debit'       => $transaction->total,
                'credit'      => 0,
                'description' => 'مبيعات كاشير',
                'sort_order'  => 1,
            ]);

            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $salesAccount->id,
                'debit'       => 0,
                'credit'      => $transaction->total,
                'description' => "إيراد POS — {$transaction->transaction_number}",
                'sort_order'  => 2,
            ]);

            return $entry;
        });
    }

    /**
     * قيد سند صرف/قبض يدوي
     */
    public function createVoucherEntry(Voucher $voucher): JournalEntry
    {
        return DB::transaction(function () use ($voucher) {

            $entry = JournalEntry::create([
                'entry_number'   => JournalEntry::generateNumber(),
                'date'           => $voucher->date->toDateString(),
                'description'    => "{$voucher->type_label} #{$voucher->voucher_number} — {$voucher->description}",
                'user_id'        => auth()->id() ?? $voucher->user_id,
                'status'         => 'posted',
                'reference_type' => Voucher::class,
                'reference_id'   => $voucher->id,
                'source'         => 'manual',
            ]);

            if ($voucher->type === 'receipt') {
                // قبض: من حـ/الصندوق → إلى حـ/الحساب المقابل
                JournalEntryLine::create([
                    'entry_id'    => $entry->id,
                    'account_id'  => $voucher->cash_account_id,
                    'debit'       => $voucher->amount,
                    'credit'      => 0,
                    'description' => 'استلام نقدي',
                    'sort_order'  => 1,
                ]);
                JournalEntryLine::create([
                    'entry_id'    => $entry->id,
                    'account_id'  => $voucher->account_id,
                    'debit'       => 0,
                    'credit'      => $voucher->amount,
                    'description' => $voucher->description,
                    'sort_order'  => 2,
                ]);
            } else {
                // صرف: من حـ/الحساب المقابل → إلى حـ/الصندوق
                JournalEntryLine::create([
                    'entry_id'    => $entry->id,
                    'account_id'  => $voucher->account_id,
                    'debit'       => $voucher->amount,
                    'credit'      => 0,
                    'description' => $voucher->description,
                    'sort_order'  => 1,
                ]);
                JournalEntryLine::create([
                    'entry_id'    => $entry->id,
                    'account_id'  => $voucher->cash_account_id,
                    'debit'       => 0,
                    'credit'      => $voucher->amount,
                    'description' => 'صرف نقدي',
                    'sort_order'  => 2,
                ]);
            }

            // ربط القيد بالسند
            $voucher->update(['journal_entry_id' => $entry->id]);

            return $entry;
        });
    }

    // ─── التقارير المالية ─────────────────────────────────────────

    /**
     * ميزان المراجعة
     */
    public function getTrialBalance(?string $fromDate = null, ?string $toDate = null): array
    {
        $accounts = Account::where('is_leaf', true)
            ->where('is_active', true)
            ->with(['journalLines' => function ($q) use ($fromDate, $toDate) {
                $q->whereHas('entry', function ($eq) use ($fromDate, $toDate) {
                    $eq->where('status', 'posted');
                    if ($fromDate) $eq->whereDate('date', '>=', $fromDate);
                    if ($toDate)   $eq->whereDate('date', '<=', $toDate);
                });
            }])
            ->orderBy('code')
            ->get();

        $rows = [];
        $totalDebit  = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $debit  = $account->journalLines->sum('debit')  + ($account->opening_balance_type === 'debit'  ? $account->opening_balance : 0);
            $credit = $account->journalLines->sum('credit') + ($account->opening_balance_type === 'credit' ? $account->opening_balance : 0);

            if ($debit == 0 && $credit == 0) continue;

            $balance = abs($debit - $credit);
            $balanceType = $debit >= $credit ? 'debit' : 'credit';

            $rows[] = [
                'account'      => $account,
                'debit'        => $debit,
                'credit'       => $credit,
                'balance'      => $balance,
                'balance_type' => $balanceType,
            ];

            $totalDebit  += $debit;
            $totalCredit += $credit;
        }

        return [
            'rows'         => $rows,
            'total_debit'  => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'is_balanced'  => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /**
     * قائمة الدخل (الإيرادات - المصروفات)
     */
    public function getIncomeStatement(string $fromDate, string $toDate): array
    {
        $revenues = $this->getAccountTotals('revenue', $fromDate, $toDate);
        $expenses = $this->getAccountTotals('expense', $fromDate, $toDate);

        $totalRevenues = collect($revenues)->sum('balance');
        $totalExpenses = collect($expenses)->sum('balance');
        $netIncome     = $totalRevenues - $totalExpenses;

        return [
            'revenues'       => $revenues,
            'expenses'       => $expenses,
            'total_revenues' => round($totalRevenues, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_income'     => round($netIncome, 2),
            'is_profit'      => $netIncome >= 0,
            'from_date'      => $fromDate,
            'to_date'        => $toDate,
        ];
    }

    /**
     * الميزانية العمومية
     */
    public function getBalanceSheet(string $toDate): array
    {
        $assets      = $this->getAccountTotals('asset',     null, $toDate);
        $liabilities = $this->getAccountTotals('liability', null, $toDate);
        $equity      = $this->getAccountTotals('equity',    null, $toDate);

        $totalAssets      = collect($assets)->sum('balance');
        $totalLiabilities = collect($liabilities)->sum('balance');
        $totalEquity      = collect($equity)->sum('balance');

        return [
            'assets'            => $assets,
            'liabilities'       => $liabilities,
            'equity'            => $equity,
            'total_assets'      => round($totalAssets, 2),
            'total_liabilities' => round($totalLiabilities, 2),
            'total_equity'      => round($totalEquity, 2),
            'is_balanced'       => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
            'to_date'           => $toDate,
        ];
    }

    /**
     * دفتر الأستاذ لحساب معين
     */
    public function getLedger(Account $account, ?string $fromDate = null, ?string $toDate = null): array
    {
        $query = JournalEntryLine::where('account_id', $account->id)
            ->with(['entry'])
            ->whereHas('entry', function ($q) use ($fromDate, $toDate) {
                $q->where('status', 'posted');
                if ($fromDate) $q->whereDate('date', '>=', $fromDate);
                if ($toDate)   $q->whereDate('date', '<=', $toDate);
            })
            ->orderBy('created_at');

        $lines    = $query->get();
        $balance  = 0;
        $rows     = [];

        $openingDebit  = $account->opening_balance_type === 'debit'  ? $account->opening_balance : 0;
        $openingCredit = $account->opening_balance_type === 'credit' ? $account->opening_balance : 0;

        if ($account->normal_balance === 'debit') {
            $balance = $openingDebit - $openingCredit;
        } else {
            $balance = $openingCredit - $openingDebit;
        }

        foreach ($lines as $line) {
            if ($account->normal_balance === 'debit') {
                $balance += $line->debit - $line->credit;
            } else {
                $balance += $line->credit - $line->debit;
            }

            $rows[] = [
                'date'        => $line->entry->date,
                'description' => $line->description ?: $line->entry->description,
                'entry'       => $line->entry,
                'debit'       => $line->debit,
                'credit'      => $line->credit,
                'balance'     => round($balance, 2),
            ];
        }

        return [
            'account'    => $account,
            'rows'       => $rows,
            'total_debit'  => round($lines->sum('debit'), 2),
            'total_credit' => round($lines->sum('credit'), 2),
            'balance'      => round($balance, 2),
            'from_date'    => $fromDate,
            'to_date'      => $toDate,
        ];
    }

    // ─── Private Helpers ─────────────────────────────────────────

    private function findAccount(string $code): Account
    {
        $account = Account::where('code', $code)->first();

        if (! $account) {
            throw new \Exception("الحساب برمز [{$code}] غير موجود. يرجى تشغيل ChartOfAccountsSeeder أولاً.");
        }

        return $account;
    }

    // ─── قيد استلام البضاعة (مشتريات) ──────────────────────────────

    /**
     * من حـ/المخزون → إلى حـ/الذمم الدائنة (موردون)
     */
    public function createPurchaseEntry(\App\Models\PurchaseOrder $order, \App\Models\GoodsReceipt $receipt): JournalEntry
    {
        return DB::transaction(function () use ($order, $receipt) {

            $inventoryAccount = $this->findAccount(self::ACCOUNT_INVENTORY);
            $payablesAccount  = $this->findAccount(self::ACCOUNT_PAYABLES);

            $totalValue = $receipt->totalValue();

            $entry = JournalEntry::create([
                'entry_number'   => JournalEntry::generateNumber(),
                'date'           => $receipt->received_date->toDateString(),
                'description'    => "استلام بضاعة — أمر شراء #{$order->order_number}",
                'user_id'        => auth()->id() ?? $order->user_id,
                'status'         => 'posted',
                'reference_type' => \App\Models\GoodsReceipt::class,
                'reference_id'   => $receipt->id,
                'source'         => 'purchase',
            ]);

            // مدين: المخزون
            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $inventoryAccount->id,
                'debit'       => $totalValue,
                'credit'      => 0,
                'description' => "بضاعة واردة — {$order->order_number}",
                'sort_order'  => 1,
            ]);

            // دائن: ذمم موردون
            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $payablesAccount->id,
                'debit'       => 0,
                'credit'      => $totalValue,
                'description' => "مستحق للمورد — {$order->supplier->name}",
                'sort_order'  => 2,
            ]);

            // تحديث رصيد المورد
            $order->supplier->increment('balance', $totalValue);

            return $entry;
        });
    }

    // ─── قيد دفعة للمورد ────────────────────────────────────────────

    /**
     * من حـ/الذمم الدائنة → إلى حـ/الصندوق أو البنك
     */
    public function createSupplierPaymentEntry(\App\Models\SupplierPayment $payment): JournalEntry
    {
        return DB::transaction(function () use ($payment) {

            $payablesAccount = $this->findAccount(self::ACCOUNT_PAYABLES);
            $cashAccount     = $this->findAccount(self::ACCOUNT_CASH);

            $entry = JournalEntry::create([
                'entry_number'   => JournalEntry::generateNumber(),
                'date'           => $payment->payment_date->toDateString(),
                'description'    => "دفعة للمورد — {$payment->supplier->name} — {$payment->payment_number}",
                'user_id'        => auth()->id() ?? $payment->user_id,
                'status'         => 'posted',
                'reference_type' => \App\Models\SupplierPayment::class,
                'reference_id'   => $payment->id,
                'source'         => 'supplier_payment',
            ]);

            // مدين: ذمم موردون (تخفيض الالتزام)
            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $payablesAccount->id,
                'debit'       => $payment->amount,
                'credit'      => 0,
                'description' => "سداد مستحقات — {$payment->supplier->name}",
                'sort_order'  => 1,
            ]);

            // دائن: الصندوق
            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $cashAccount->id,
                'debit'       => 0,
                'credit'      => $payment->amount,
                'description' => "صرف نقدي — {$payment->payment_number}",
                'sort_order'  => 2,
            ]);

            return $entry;
        });
    }

    // ─── قيد مرتجع مشتريات ──────────────────────────────────────────

    /**
     * من حـ/الذمم الدائنة → إلى حـ/المخزون
     */
    public function createPurchaseReturnEntry(\App\Models\PurchaseOrder $order, float $amount): JournalEntry
    {
        return DB::transaction(function () use ($order, $amount) {

            $payablesAccount  = $this->findAccount(self::ACCOUNT_PAYABLES);
            $inventoryAccount = $this->findAccount(self::ACCOUNT_INVENTORY);

            $entry = JournalEntry::create([
                'entry_number'   => JournalEntry::generateNumber(),
                'date'           => now()->toDateString(),
                'description'    => "مرتجع مشتريات — أمر شراء #{$order->order_number}",
                'user_id'        => auth()->id() ?? $order->user_id,
                'status'         => 'posted',
                'reference_type' => \App\Models\PurchaseOrder::class,
                'reference_id'   => $order->id,
                'source'         => 'purchase_return',
            ]);

            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $payablesAccount->id,
                'debit'       => $amount,
                'credit'      => 0,
                'description' => "مرتجع — تخفيض ذمة المورد",
                'sort_order'  => 1,
            ]);

            JournalEntryLine::create([
                'entry_id'    => $entry->id,
                'account_id'  => $inventoryAccount->id,
                'debit'       => 0,
                'credit'      => $amount,
                'description' => "بضاعة مرتجعة للمورد",
                'sort_order'  => 2,
            ]);

            $order->supplier->decrement('balance', $amount);

            return $entry;
        });
    }

    private function getAccountTotals(string $type, ?string $fromDate, ?string $toDate): array
    {
        $accounts = Account::where('type', $type)
            ->where('is_leaf', true)
            ->where('is_active', true)
            ->with(['journalLines' => function ($q) use ($fromDate, $toDate) {
                $q->whereHas('entry', function ($eq) use ($fromDate, $toDate) {
                    $eq->where('status', 'posted');
                    if ($fromDate) $eq->whereDate('date', '>=', $fromDate);
                    if ($toDate)   $eq->whereDate('date', '<=', $toDate);
                });
            }])
            ->orderBy('code')
            ->get();

        $rows = [];

        foreach ($accounts as $account) {
            $debit  = $account->journalLines->sum('debit');
            $credit = $account->journalLines->sum('credit');

            $openingDebit  = $account->opening_balance_type === 'debit'  ? (float)$account->opening_balance : 0;
            $openingCredit = $account->opening_balance_type === 'credit' ? (float)$account->opening_balance : 0;

            $totalDebit  = $debit  + $openingDebit;
            $totalCredit = $credit + $openingCredit;

            $balance = $account->normal_balance === 'debit'
                ? $totalDebit - $totalCredit
                : $totalCredit - $totalDebit;

            if (abs($balance) < 0.01) continue;

            $rows[] = [
                'account' => $account,
                'debit'   => round($totalDebit, 2),
                'credit'  => round($totalCredit, 2),
                'balance' => round(abs($balance), 2),
            ];
        }

        return $rows;
    }
}
