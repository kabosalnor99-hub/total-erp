{{-- المسار: resources/views/reports/index.blade.php --}}

@extends('layouts.app')

@section('title', __('reports.title'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.title') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('reports.subtitle') }}</p>
    </div>


    {{-- ═══════════════════ بطاقة التحليل الذكي ═══════════════════ --}}
    <div style="background:linear-gradient(135deg,#0d2137,#1a3a4a);
                border:1px solid #0d9488;border-radius:16px;padding:20px;margin-bottom:24px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <h3 style="color:#4FB3C0;font-size:16px;font-weight:800;margin:0">🤖 تحليل ذكي للتقارير</h3>
            <button onclick="loadAiAnalysis()" id="refresh-analysis"
                style="background:#0d9488;color:#fff;border:none;border-radius:8px;
                       padding:6px 14px;cursor:pointer;font-size:12px;font-family:inherit">
                🔄 تحديث
            </button>
        </div>
        <div id="ai-report-text" style="color:#c8e6ea;font-size:13px;line-height:1.8">
            اضغط تحديث لعرض التحليل الذكي...
        </div>
    </div>

    <script>
    function loadAiAnalysis() {
        var el  = document.getElementById('ai-report-text');
        var btn = document.getElementById('refresh-analysis');
        el.textContent = '⏳ جاري التحليل...';
        btn.disabled   = true;

        fetch('/ai/sales-insight')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            el.textContent = data.response || 'لا يوجد تحليل متاح';
            btn.disabled   = false;
        })
        .catch(function() {
            el.textContent = '❌ تعذّر الاتصال بالمساعد الذكي';
            btn.disabled   = false;
        });
    }
    document.addEventListener('DOMContentLoaded', function() { loadAiAnalysis(); });
    </script>

    {{-- Reports Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- ================== FINANCIAL ================== --}}
        <div class="xl:col-span-4">
            <h2 class="text-base font-semibold text-gray-600 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                {{ __('reports.group_financial') }}
            </h2>
        </div>

        @php
        $financialReports = [
            ['route' => 'reports.trial-balance',    'icon' => 'scale',           'color' => 'blue',   'title' => __('reports.trial_balance'),    'desc' => __('reports.trial_balance_desc')],
            ['route' => 'reports.income-statement', 'icon' => 'chart-bar',       'color' => 'green',  'title' => __('reports.income_statement'),  'desc' => __('reports.income_statement_desc')],
            ['route' => 'reports.balance-sheet',    'icon' => 'building-library','color' => 'purple', 'title' => __('reports.balance_sheet'),     'desc' => __('reports.balance_sheet_desc')],
            ['route' => 'reports.cash-flow',        'icon' => 'banknotes',       'color' => 'teal',   'title' => __('reports.cash_flow'),         'desc' => __('reports.cash_flow_desc')],
            ['route' => 'reports.general-ledger',   'icon' => 'book-open',       'color' => 'indigo', 'title' => __('reports.general_ledger'),    'desc' => __('reports.general_ledger_desc')],
        ];
        @endphp

        @foreach($financialReports as $report)
        @canPermission('reports.view')
        <a href="{{ route($report['route']) }}"
            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-{{ $report['color'] }}-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $report['color'] }}-50 flex items-center justify-center flex-shrink-0 group-hover:bg-{{ $report['color'] }}-100 transition-colors">
                    <x-dynamic-component :component="'heroicon-o-' . $report['icon']" class="w-5 h-5 text-{{ $report['color'] }}-600"/>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">{{ $report['title'] }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $report['desc'] }}</p>
                </div>
            </div>
        </a>
        @endcanPermission
        @endforeach

        {{-- ================== SALES ================== --}}
        <div class="xl:col-span-4 mt-4">
            <h2 class="text-base font-semibold text-gray-600 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                {{ __('reports.group_sales') }}
            </h2>
        </div>

        @php
        $salesReports = [
            ['route' => 'reports.sales-summary',     'icon' => 'chart-bar',         'color' => 'green',  'title' => __('reports.sales_summary'),     'desc' => __('reports.sales_summary_desc')],
            ['route' => 'reports.sales-by-customer', 'icon' => 'user-group',         'color' => 'emerald','title' => __('reports.sales_by_customer'),  'desc' => __('reports.sales_by_customer_desc')],
            ['route' => 'reports.sales-by-product',  'icon' => 'cube',               'color' => 'lime',   'title' => __('reports.sales_by_product'),   'desc' => __('reports.sales_by_product_desc')],
            ['route' => 'invoices.aging',  'icon' => 'exclamation-circle', 'color' => 'red',    'title' => __('reports.overdue_invoices'),   'desc' => __('reports.overdue_invoices_desc')],
        ];
        @endphp

        @foreach($salesReports as $report)
        @canPermission('reports.view')
        <a href="{{ route($report['route']) }}"
            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-{{ $report['color'] }}-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $report['color'] }}-50 flex items-center justify-center flex-shrink-0 group-hover:bg-{{ $report['color'] }}-100 transition-colors">
                    <x-dynamic-component :component="'heroicon-o-' . $report['icon']" class="w-5 h-5 text-{{ $report['color'] }}-600"/>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">{{ $report['title'] }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $report['desc'] }}</p>
                </div>
            </div>
        </a>
        @endcanPermission
        @endforeach

        {{-- ================== INVENTORY ================== --}}
        <div class="xl:col-span-4 mt-4">
            <h2 class="text-base font-semibold text-gray-600 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-yellow-500 inline-block"></span>
                {{ __('reports.group_inventory') }}
            </h2>
        </div>

        @php
        $inventoryReports = [
            ['route' => 'reports.stock-status',     'icon' => 'archive-box',    'color' => 'yellow', 'title' => __('reports.stock_status'),    'desc' => __('reports.stock_status_desc')],
            ['route' => 'reports.low-stock',        'icon' => 'exclamation-triangle', 'color' => 'orange','title' => __('reports.low_stock'),  'desc' => __('reports.low_stock_desc')],
            ['route' => 'reports.stock-movements',  'icon' => 'arrows-right-left','color' => 'amber', 'title' => __('reports.stock_movements'), 'desc' => __('reports.stock_movements_desc')],
            ['route' => 'reports.slow-moving',      'icon' => 'clock',           'color' => 'stone',  'title' => __('reports.slow_moving'),     'desc' => __('reports.slow_moving_desc')],
        ];
        @endphp

        @foreach($inventoryReports as $report)
        @canPermission('reports.view')
        <a href="{{ route($report['route']) }}"
            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-{{ $report['color'] }}-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $report['color'] }}-50 flex items-center justify-center flex-shrink-0 group-hover:bg-{{ $report['color'] }}-100 transition-colors">
                    <x-dynamic-component :component="'heroicon-o-' . $report['icon']" class="w-5 h-5 text-{{ $report['color'] }}-600"/>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">{{ $report['title'] }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $report['desc'] }}</p>
                </div>
            </div>
        </a>
        @endcanPermission
        @endforeach

        {{-- ================== HR ================== --}}
        <div class="xl:col-span-4 mt-4">
            <h2 class="text-base font-semibold text-gray-600 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-purple-500 inline-block"></span>
                {{ __('reports.group_hr') }}
            </h2>
        </div>

        @php
        $hrReports = [
            ['route' => 'reports.payroll-summary', 'icon' => 'currency-dollar', 'color' => 'purple', 'title' => __('reports.payroll_summary'),  'desc' => __('reports.payroll_summary_desc')],
            ['route' => 'reports.leave',           'icon' => 'calendar-days',   'color' => 'violet', 'title' => __('reports.leave_report'),     'desc' => __('reports.leave_report_desc')],
            ['route' => 'reports.attendance',      'icon' => 'clock',           'color' => 'fuchsia','title' => __('reports.attendance_report'),'desc' => __('reports.attendance_report_desc')],
        ];
        @endphp

        @foreach($hrReports as $report)
        @canPermission('reports.view')
        <a href="{{ route($report['route']) }}"
            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-{{ $report['color'] }}-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $report['color'] }}-50 flex items-center justify-center flex-shrink-0 group-hover:bg-{{ $report['color'] }}-100 transition-colors">
                    <x-dynamic-component :component="'heroicon-o-' . $report['icon']" class="w-5 h-5 text-{{ $report['color'] }}-600"/>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">{{ $report['title'] }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $report['desc'] }}</p>
                </div>
            </div>
        </a>
        @endcanPermission
        @endforeach

        {{-- ================== PURCHASES ================== --}}
        <div class="xl:col-span-4 mt-4">
            <h2 class="text-base font-semibold text-gray-600 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-teal-500 inline-block"></span>
                {{ __('reports.group_purchases') }}
            </h2>
        </div>

        @php
        $purchaseReports = [
            ['route' => 'reports.purchase-summary',   'icon' => 'shopping-cart',   'color' => 'teal',  'title' => __('reports.purchase_summary'),   'desc' => __('reports.purchase_summary_desc')],
            ['route' => 'reports.supplier-statement', 'icon' => 'truck',            'color' => 'cyan',  'title' => __('reports.supplier_statement'), 'desc' => __('reports.supplier_statement_desc')],
        ];
        @endphp

        @foreach($purchaseReports as $report)
        @canPermission('reports.view')
        <a href="{{ route($report['route']) }}"
            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-{{ $report['color'] }}-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $report['color'] }}-50 flex items-center justify-center flex-shrink-0 group-hover:bg-{{ $report['color'] }}-100 transition-colors">
                    <x-dynamic-component :component="'heroicon-o-' . $report['icon']" class="w-5 h-5 text-{{ $report['color'] }}-600"/>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">{{ $report['title'] }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $report['desc'] }}</p>
                </div>
            </div>
        </a>
        @endcanPermission
        @endforeach

    </div>
</div>
@endsection
