<?php

// المسار: app/Services/ReportService.php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\PurchaseOrder;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Leave;
use App\Models\Attendance;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    // ================================================================
    // FINANCIAL REPORTS
    // ================================================================

    /**
     * Trial Balance: debit/credit totals per account
     */
    public function trialBalance(string $dateFrom, string $dateTo): array
    {
        $accounts = Account::with(['journalEntryLines' => function ($q) use ($dateFrom, $dateTo) {
            $q->whereHas('journalEntry', fn($je) => $je
                ->whereDate('date', '>=', $dateFrom)
                ->whereDate('date', '<=', $dateTo)
                ->where('status', 'posted')
            );
        }])->orderBy('code')->get();

        $rows              = [];
        $totalDebit        = 0;
        $totalCredit       = 0;

        foreach ($accounts as $account) {
            $debit  = $account->journalEntryLines->sum('debit');
            $credit = $account->journalEntryLines->sum('credit');

            if ($debit == 0 && $credit == 0) continue;

            $rows[] = [
                'code'    => $account->code,
                'name'    => app()->getLocale() === 'ar' ? $account->name_ar : $account->name_en,
                'type'    => $account->type,
                'debit'   => $debit,
                'credit'  => $credit,
                'balance' => $debit - $credit,
            ];

            $totalDebit  += $debit;
            $totalCredit += $credit;
        }

        return [
            'rows'         => $rows,
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
            'date_from'    => $dateFrom,
            'date_to'      => $dateTo,
            'is_balanced'  => round($totalDebit, 2) === round($totalCredit, 2),
        ];
    }

    /**
     * Income Statement: revenues minus expenses
     */
    public function incomeStatement(string $dateFrom, string $dateTo): array
    {
        $revenues = Account::where('type', 'revenue')
            ->with(['journalEntryLines' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereHas('journalEntry', fn($je) => $je
                    ->whereDate('date', '>=', $dateFrom)
                    ->whereDate('date', '<=', $dateTo)
                    ->where('status', 'posted')
                );
            }])->get()->map(fn($a) => [
                'code'    => $a->code,
                'name'    => app()->getLocale() === 'ar' ? $a->name_ar : $a->name_en,
                'amount'  => $a->journalEntryLines->sum('credit') - $a->journalEntryLines->sum('debit'),
            ])->filter(fn($r) => $r['amount'] != 0);

        $expenses = Account::where('type', 'expense')
            ->with(['journalEntryLines' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereHas('journalEntry', fn($je) => $je
                    ->whereDate('date', '>=', $dateFrom)
                    ->whereDate('date', '<=', $dateTo)
                    ->where('status', 'posted')
                );
            }])->get()->map(fn($a) => [
                'code'    => $a->code,
                'name'    => app()->getLocale() === 'ar' ? $a->name_ar : $a->name_en,
                'amount'  => $a->journalEntryLines->sum('debit') - $a->journalEntryLines->sum('credit'),
            ])->filter(fn($r) => $r['amount'] != 0);

        $totalRevenue = $revenues->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $netProfit    = $totalRevenue - $totalExpense;

        return [
            'revenues'      => $revenues->values()->toArray(),
            'expenses'      => $expenses->values()->toArray(),
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_profit'    => $netProfit,
            'is_profit'     => $netProfit >= 0,
            'date_from'     => $dateFrom,
            'date_to'       => $dateTo,
        ];
    }

    /**
     * Balance Sheet: assets, liabilities, equity at a given date
     */
    public function balanceSheet(string $date): array
    {
        $types = ['asset', 'liability', 'equity'];
        $data  = [];

        // Get all journal entry lines up to the given date in one query
        $journalEntryIds = \App\Models\JournalEntry::whereDate('date', '<=', $date)
            ->where('status', 'posted')
            ->pluck('id');

        foreach ($types as $type) {
            $accounts = Account::where('type', $type)
                ->with(['journalEntryLines' => function ($q) use ($journalEntryIds) {
                    $q->whereIn('entry_id', $journalEntryIds);
                }])->get()->map(fn($a) => [
                    'code'    => $a->code,
                    'name'    => app()->getLocale() === 'ar' ? $a->name_ar : $a->name_en,
                    'balance' => $a->journalEntryLines->sum('debit') - $a->journalEntryLines->sum('credit'),
                ])->filter(fn($r) => $r['balance'] != 0)->values();

            $data[$type] = [
                'rows'  => $accounts->toArray(),
                'total' => $accounts->sum('balance'),
            ];
        }

        $data['date']             = $date;
        $data['total_assets']     = $data['asset']['total'];
        $data['total_liabilities']= $data['liability']['total'];
        $data['total_equity']     = $data['equity']['total'];
        $data['is_balanced']      = round($data['total_assets'], 2) === round($data['total_liabilities'] + $data['total_equity'], 2);

        return $data;
    }

    /**
     * General Ledger: movements of a specific account
     */
    public function generalLedger(?int $accountId, string $dateFrom, string $dateTo): array
    {
        $query = JournalEntryLine::with(['journalEntry', 'account'])
            ->whereHas('journalEntry', fn($je) => $je
                ->whereDate('date', '>=', $dateFrom)
                ->whereDate('date', '<=', $dateTo)
                ->where('status', 'posted')
            );

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        $lines   = $query->orderBy('created_at')->get();
        $balance = 0;
        $rows    = [];

        foreach ($lines as $line) {
            $balance += $line->debit - $line->credit;
            $rows[] = [
                'date'        => $line->journalEntry->date->format('Y-m-d'),
                'reference'   => $line->journalEntry->reference_number,
                'description' => $line->description ?? $line->journalEntry->description,
                'debit'       => $line->debit,
                'credit'      => $line->credit,
                'balance'     => $balance,
            ];
        }

        return [
            'rows'       => $rows,
            'date_from'  => $dateFrom,
            'date_to'    => $dateTo,
            'account_id' => $accountId,
            'accounts'   => Account::orderBy('code')->get(),
        ];
    }

    /**
     * Cash Flow Statement
     */
    public function cashFlow(string $dateFrom, string $dateTo): array
    {
        // Operating: Sales receipts
        $salesReceipts = Voucher::where('type', 'receipt')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->sum('amount');

        // Operating: Purchase payments
        $purchasePayments = Voucher::where('type', 'payment')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->sum('amount');

        // Operating: Payroll
        $payrollPayments = Payroll::whereDate('payment_date', '>=', $dateFrom)
            ->whereDate('payment_date', '<=', $dateTo)
            ->where('status', 'paid')
            ->sum('net_salary');

        $operatingCashFlow = $salesReceipts - $purchasePayments - $payrollPayments;

        return [
            'operating' => [
                'sales_receipts'   => $salesReceipts,
                'purchase_payments'=> $purchasePayments,
                'payroll_payments' => $payrollPayments,
                'net'              => $operatingCashFlow,
            ],
            'net_cash_flow' => $operatingCashFlow,
            'date_from'     => $dateFrom,
            'date_to'       => $dateTo,
        ];
    }

    // ================================================================
    // SALES REPORTS
    // ================================================================

    /**
     * Sales summary with totals, average, and trend
     */
    public function salesSummary(string $dateFrom, string $dateTo): array
    {
        $invoices = Invoice::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('
                COUNT(*) as total_invoices,
                SUM(total) as total_amount,
                SUM(discount_amount) as total_discount,
                SUM(tax_amount) as total_tax,
                SUM(paid_amount) as total_paid,
                SUM(total - paid_amount) as total_due,
                AVG(total) as avg_invoice
            ')
            ->first();

        $monthly = Invoice::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'summary'   => $invoices,
            'monthly'   => $monthly,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ];
    }

    /**
     * Sales grouped by customer
     */
    public function salesByCustomer(string $dateFrom, string $dateTo): array
    {
        $rows = Invoice::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->with('customer')
            ->selectRaw('customer_id, COUNT(*) as total_invoices, SUM(total) as total_amount, SUM(total - paid_amount) as balance')
            ->groupBy('customer_id')
            ->orderByDesc('total_amount')
            ->get();

        return [
            'rows'      => $rows,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ];
    }

    /**
     * Sales grouped by product
     */
    public function salesByProduct(string $dateFrom, string $dateTo): array
    {
        $rows = InvoiceItem::whereHas('invoice', fn($q) => $q
            ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo)
            ->where('status', '!=', 'cancelled')
        )->with('product')
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_amount')
            ->groupBy('product_id')
            ->orderByDesc('total_amount')
            ->get();

        return [
            'rows'      => $rows,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ];
    }

    /**
     * Overdue invoices (due_date < today and not fully paid)
     */
    public function overdueInvoices(): array
    {
        $rows = Invoice::where('due_date', '<', now())
            ->where('status', '!=', 'cancelled')
            ->whereRaw('paid_amount < total')
            ->with('customer')
            ->orderBy('due_date')
            ->get()
            ->map(fn($inv) => [
                'id'           => $inv->id,
                'number'       => $inv->invoice_number,
                'customer'     => $inv->customer->name,
                'total'        => $inv->total,
                'paid'         => $inv->paid_amount,
                'due'          => $inv->total - $inv->paid_amount,
                'due_date'     => $inv->due_date->format('Y-m-d'),
                'overdue_days' => $inv->due_date->diffInDays(now()),
            ]);

        return ['rows' => $rows->toArray()];
    }

    // ================================================================
    // INVENTORY REPORTS
    // ================================================================

    /**
     * Current stock status for all products
     */
    public function stockStatus(?int $warehouseId = null, ?int $categoryId = null): array
    {
        $query = Product::with(['category', 'stockMovements'])
            ->withSum([
                'stockMovements as stock_in' => fn($q) => $q->where('type', 'in')
            ], 'quantity')
            ->withSum([
                'stockMovements as stock_out' => fn($q) => $q->where('type', 'out')
            ], 'quantity');

        if ($categoryId) $query->where('category_id', $categoryId);

        $products = $query->orderBy('name_ar')->get()->map(fn($p) => [
            'sku'           => $p->sku,
            'name'          => app()->getLocale() === 'ar' ? $p->name_ar : $p->name_en,
            'category'      => $p->category->name_ar ?? '-',
            'stock_in'      => $p->stock_in ?? 0,
            'stock_out'     => $p->stock_out ?? 0,
            'current_stock' => ($p->stock_in ?? 0) - ($p->stock_out ?? 0),
            'reorder_point' => $p->reorder_point,
            'is_low'        => (($p->stock_in ?? 0) - ($p->stock_out ?? 0)) <= $p->reorder_point,
            'unit_cost'     => $p->purchase_price,
            'stock_value'   => (($p->stock_in ?? 0) - ($p->stock_out ?? 0)) * $p->purchase_price,
        ]);

        return [
            'rows'        => $products->toArray(),
            'total_value' => $products->sum('stock_value'),
        ];
    }

    /**
     * Products below reorder point
     */
    public function lowStockProducts(): array
    {
        $products = Product::with('category')
            ->withSum(['stockMovements as stock_in' => fn($q) => $q->where('type', 'in')], 'quantity')
            ->withSum(['stockMovements as stock_out' => fn($q) => $q->where('type', 'out')], 'quantity')
            ->get()
            ->filter(fn($p) => (($p->stock_in ?? 0) - ($p->stock_out ?? 0)) <= $p->reorder_point)
            ->map(fn($p) => [
                'id'            => $p->id,
                'sku'           => $p->sku,
                'name'          => app()->getLocale() === 'ar' ? $p->name_ar : $p->name_en,
                'current_stock' => ($p->stock_in ?? 0) - ($p->stock_out ?? 0),
                'reorder_point' => $p->reorder_point,
                'shortage'      => $p->reorder_point - (($p->stock_in ?? 0) - ($p->stock_out ?? 0)),
            ]);

        return ['rows' => $products->values()->toArray()];
    }

    /**
     * Stock movements for a product in a date range
     */
    public function stockMovements(?int $productId, string $dateFrom, string $dateTo): array
    {
        $query = StockMovement::with(['product', 'warehouse'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at');

        if ($productId) $query->where('product_id', $productId);

        $rows = $query->get();

        return [
            'rows'      => $rows,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'products'  => Product::orderBy('name_ar')->get(),
        ];
    }

    /**
     * Products with no movement in the last $days days
     */
    public function slowMovingProducts(int $days = 90): array
    {
        $cutoff = now()->subDays($days)->toDateString();

        $products = Product::with('category')
            ->whereDoesntHave('stockMovements', fn($q) => $q->whereDate('created_at', '>=', $cutoff))
            ->get()
            ->map(fn($p) => [
                'sku'           => $p->sku,
                'name'          => app()->getLocale() === 'ar' ? $p->name_ar : $p->name_en,
                'category'      => $p->category->name_ar ?? '-',
                'last_movement' => optional($p->stockMovements()->latest('created_at')->first())->created_at?->format('Y-m-d') ?? __('reports.never'),
            ]);

        return [
            'rows' => $products->toArray(),
            'days' => $days,
        ];
    }

    // ================================================================
    // HR REPORTS
    // ================================================================

    /**
     * Payroll summary for a given month/year
     */
    public function payrollSummary(int $month, int $year): array
    {
        $payrolls = Payroll::with('employee.department')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $summary = [
            'total_basic'     => $payrolls->sum('basic_salary'),
            'total_allowances'=> $payrolls->sum('total_allowances'),
            'total_deductions'=> $payrolls->sum('total_deductions'),
            'total_net'       => $payrolls->sum('net_salary'),
            'paid_count'      => $payrolls->where('status', 'paid')->count(),
            'pending_count'   => $payrolls->where('status', 'pending')->count(),
        ];

        return [
            'rows'    => $payrolls,
            'summary' => $summary,
            'month'   => $month,
            'year'    => $year,
        ];
    }

    /**
     * Leave report for a date range
     */
    public function leaveReport(string $dateFrom, string $dateTo): array
    {
        $leaves = Leave::with('employee')
            ->whereBetween('start_date', [$dateFrom, $dateTo])
            ->orderBy('start_date')
            ->get();

        return [
            'rows'      => $leaves,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ];
    }

    /**
     * Attendance summary per employee for a month
     */
    public function attendanceReport(int $month, int $year): array
    {
        $employees = Employee::where('status', 'active')
            ->with(['attendances' => fn($q) => $q->where('month', $month)->where('year', $year)])
            ->get()
            ->map(fn($e) => [
                'name'          => $e->full_name,
                'department'    => $e->department->name_ar ?? '-',
                'present_days'  => $e->attendances->where('status', 'present')->count(),
                'absent_days'   => $e->attendances->where('status', 'absent')->count(),
                'late_days'     => $e->attendances->where('status', 'late')->count(),
                'leave_days'    => $e->attendances->where('status', 'leave')->count(),
            ]);

        return [
            'rows'  => $employees->toArray(),
            'month' => $month,
            'year'  => $year,
        ];
    }

    // ================================================================
    // PURCHASE REPORTS
    // ================================================================

    /**
     * Purchase orders summary
     */
    public function purchaseSummary(string $dateFrom, string $dateTo): array
    {
        $orders = PurchaseOrder::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->with('supplier')
            ->selectRaw('supplier_id, COUNT(*) as total_orders, SUM(total) as total_amount')
            ->groupBy('supplier_id')
            ->orderByDesc('total_amount')
            ->get();

        $total = PurchaseOrder::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        return [
            'rows'      => $orders,
            'total'     => $total,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ];
    }

    /**
     * Supplier account statement
     */
    public function supplierStatement(int $supplierId, string $dateFrom, string $dateTo): array
    {
        $orders = PurchaseOrder::where('supplier_id', $supplierId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->get();

        $payments = SupplierPayment::where('supplier_id', $supplierId)
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->get();

        $totalOrders   = $orders->sum('total');
        $totalPayments = $payments->sum('amount');
        $balance       = $totalOrders - $totalPayments;

        return [
            'orders'         => $orders,
            'payments'       => $payments,
            'total_orders'   => $totalOrders,
            'total_payments' => $totalPayments,
            'balance'        => $balance,
            'supplier'       => Supplier::findOrFail($supplierId),
            'date_from'      => $dateFrom,
            'date_to'        => $dateTo,
        ];
    }
}
