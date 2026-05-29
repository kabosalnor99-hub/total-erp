<?php

// المسار: app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService)
    {
        $this->middleware(['auth', 'permission:reports.view']);
    }

    // -------------------------------------------------------
    // Reports index
    // -------------------------------------------------------

    public function index(): View
    {
        return view('reports.index');
    }

    // -------------------------------------------------------
    // Financial Reports
    // -------------------------------------------------------

    public function trialBalance(Request $request): View
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        $fromDate = $request->date_from ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->date_to   ?? now()->toDateString();

        $data = $this->reportService->trialBalance($fromDate, $toDate);

        return view('reports.trial_balance', array_merge($data, [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]));
    }

    public function incomeStatement(Request $request): View
    {
        $request->validate([
            'period'    => 'nullable|in:monthly,quarterly,yearly',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
        ]);

        $fromDate = $request->date_from ?? now()->startOfYear()->toDateString();
        $toDate   = $request->date_to   ?? now()->toDateString();

        $data = $this->reportService->incomeStatement($fromDate, $toDate);

        return view('reports.income_statement', array_merge($data, [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]));
    }

    public function balanceSheet(Request $request): View
    {
        $date = $request->date ?? now()->toDateString();
        $data = $this->reportService->balanceSheet($date);

        return view('reports.balance_sheet', compact('data', 'date'));
    }

    public function generalLedger(Request $request): View
    {
        $request->validate([
            'account_id' => 'nullable|exists:accounts,id',
            'date_from'  => 'nullable|date',
            'date_to'    => 'nullable|date',
        ]);

        $data = $this->reportService->generalLedger(
            $request->account_id,
            $request->date_from ?? now()->startOfMonth()->toDateString(),
            $request->date_to   ?? now()->toDateString()
        );

        return view('reports.general_ledger', compact('data'));
    }

    public function cashFlow(Request $request): View
    {
        $data = $this->reportService->cashFlow(
            $request->date_from ?? now()->startOfMonth()->toDateString(),
            $request->date_to   ?? now()->toDateString()
        );

        return view('reports.cash_flow', compact('data'));
    }

    // -------------------------------------------------------
    // Sales Reports
    // -------------------------------------------------------

    public function salesSummary(Request $request): View
    {
        $data = $this->reportService->salesSummary(
            $request->date_from ?? now()->startOfMonth()->toDateString(),
            $request->date_to   ?? now()->toDateString()
        );

        return view('reports.sales.summary', compact('data'));
    }

    public function salesByCustomer(Request $request): View
    {
        $data = $this->reportService->salesByCustomer(
            $request->date_from ?? now()->startOfMonth()->toDateString(),
            $request->date_to   ?? now()->toDateString()
        );

        return view('reports.sales.by-customer', compact('data'));
    }

    public function salesByProduct(Request $request): View
    {
        $data = $this->reportService->salesByProduct(
            $request->date_from ?? now()->startOfMonth()->toDateString(),
            $request->date_to   ?? now()->toDateString()
        );

        return view('reports.sales.by-product', compact('data'));
    }

    public function overdueInvoices(Request $request): View
    {
        $data = $this->reportService->overdueInvoices();
        return view('reports.sales.overdue-invoices', compact('data'));
    }

    // -------------------------------------------------------
    // Inventory Reports
    // -------------------------------------------------------

    public function stockStatus(Request $request): View
    {
        $data = $this->reportService->stockStatus(
            $request->warehouse_id,
            $request->category_id
        );

        return view('reports.stock-status', compact('data'));
    }

    public function lowStockReport(): View
    {
        $data = $this->reportService->lowStockProducts();
        return view('reports.low-stock', compact('data'));
    }

    public function stockMovements(Request $request): View
    {
        $data = $this->reportService->stockMovements(
            $request->product_id,
            $request->date_from ?? now()->startOfMonth()->toDateString(),
            $request->date_to   ?? now()->toDateString()
        );

        return view('reports.movements', compact('data'));
    }

    public function slowMovingProducts(): View
    {
        $data = $this->reportService->slowMovingProducts(90);
        return view('reports.slow-moving', compact('data'));
    }

    // -------------------------------------------------------
    // HR Reports
    // -------------------------------------------------------

    public function payrollSummary(Request $request): View
    {
        $data = $this->reportService->payrollSummary(
            $request->month ?? now()->month,
            $request->year  ?? now()->year
        );

        return view('reports.payroll-summary', compact('data'));
    }

    public function leaveReport(Request $request): View
    {
        $data = $this->reportService->leaveReport(
            $request->date_from ?? now()->startOfMonth()->toDateString(),
            $request->date_to   ?? now()->toDateString()
        );

        return view('reports.leave', compact('data'));
    }

    public function attendanceReport(Request $request): View
    {
        $data = $this->reportService->attendanceReport(
            $request->month ?? now()->month,
            $request->year  ?? now()->year
        );

        return view('reports.attendance', compact('data'));
    }

    // -------------------------------------------------------
    // Purchase Reports
    // -------------------------------------------------------

    public function purchaseSummary(Request $request): View
    {
        $data = $this->reportService->purchaseSummary(
            $request->date_from ?? now()->startOfMonth()->toDateString(),
            $request->date_to   ?? now()->toDateString()
        );

        return view('reports.purchase-summary', compact('data'));
    }

    public function supplierStatement(Request $request): View
    {
        // عرض الصفحة فارغة إذا لم يُختر مورد بعد
        if (!$request->filled('supplier_id')) {
            return view('reports.supplier-statement', ['data' => []]);
        }

        $request->validate(['supplier_id' => 'required|exists:suppliers,id']);

        $data = $this->reportService->supplierStatement(
            $request->supplier_id,
            $request->date_from ?? now()->startOfYear()->toDateString(),
            $request->date_to   ?? now()->toDateString()
        );

        return view('reports.supplier-statement', compact('data'));
    }

    // -------------------------------------------------------
    // Export to PDF
    // -------------------------------------------------------

    public function exportPdf(Request $request, string $reportType): Response
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
        ]);

        $data = $this->getReportData($reportType, $request);
        $view = "reports.{$reportType}";

        $pdf = Pdf::loadView($view, compact('data'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        $filename = $reportType . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    // -------------------------------------------------------
    // Export to Excel
    // -------------------------------------------------------

    public function exportExcel(Request $request, string $reportType): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $exportClass = $this->getExportClass($reportType);
        $filename    = $reportType . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new $exportClass($request->all()), $filename);
    }

    // -------------------------------------------------------
    // Internal Helpers
    // -------------------------------------------------------

    private function getReportData(string $reportType, Request $request): array
    {
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();

        return match ($reportType) {
            'trial-balance'      => $this->reportService->trialBalance($dateFrom, $dateTo),
            'income-statement'   => $this->reportService->incomeStatement($dateFrom, $dateTo),
            'balance-sheet'      => $this->reportService->balanceSheet($dateTo),
            'cash-flow'          => $this->reportService->cashFlow($dateFrom, $dateTo),
            'sales-summary'      => $this->reportService->salesSummary($dateFrom, $dateTo),
            'sales-by-customer'  => $this->reportService->salesByCustomer($dateFrom, $dateTo),
            'sales-by-product'   => $this->reportService->salesByProduct($dateFrom, $dateTo),
            'stock-status'       => $this->reportService->stockStatus(),
            'low-stock'          => $this->reportService->lowStockProducts(),
            'payroll-summary'    => $this->reportService->payrollSummary(now()->month, now()->year),
            default              => [],
        };
    }

    private function getExportClass(string $reportType): string
    {
        $map = [
            'trial-balance'    => \App\Exports\TrialBalanceExport::class,
            'income-statement' => \App\Exports\IncomeStatementExport::class,
            'sales-summary'    => \App\Exports\SalesSummaryExport::class,
            'stock-status'     => \App\Exports\StockStatusExport::class,
            'payroll-summary'  => \App\Exports\PayrollSummaryExport::class,
        ];

        return $map[$reportType] ?? \App\Exports\GenericExport::class;
    }
}
