@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
<style>
* { font-family: 'Cairo', 'Tajawal', sans-serif; }
body { background: #eef2f7; }

/* ── KPI Cards ── */
.kpi-card {
    background: #fff;
    border-radius: 18px;
    padding: 1.25rem 1.4rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 4px 20px rgba(0,0,0,0.04);
    transition: transform .2s ease, box-shadow .2s ease;
    position: relative; overflow: hidden;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.10); }
.kpi-card::after {
    content: ''; position: absolute;
    bottom: -18px; left: -18px;
    width: 80px; height: 80px;
    border-radius: 50%;
    opacity: .06;
}
.kpi-teal   { border-top: 3px solid #146E6E; } .kpi-teal::after   { background:#146E6E; }
.kpi-blue   { border-top: 3px solid #3b82f6; } .kpi-blue::after   { background:#3b82f6; }
.kpi-amber  { border-top: 3px solid #f59e0b; } .kpi-amber::after  { background:#f59e0b; }
.kpi-rose   { border-top: 3px solid #e11d48; } .kpi-rose::after   { background:#e11d48; }
.kpi-purple { border-top: 3px solid #7c3aed; } .kpi-purple::after { background:#7c3aed; }
.kpi-green  { border-top: 3px solid #059669; } .kpi-green::after  { background:#059669; }

.kpi-icon { width:46px; height:46px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.15rem; flex-shrink:0; }
.kpi-teal   .kpi-icon { background:#e6f4f4; color:#146E6E; }
.kpi-blue   .kpi-icon { background:#dbeafe; color:#2563eb; }
.kpi-amber  .kpi-icon { background:#fef3c7; color:#d97706; }
.kpi-rose   .kpi-icon { background:#ffe4e6; color:#e11d48; }
.kpi-purple .kpi-icon { background:#ede9fe; color:#7c3aed; }
.kpi-green  .kpi-icon { background:#d1fae5; color:#059669; }

.kpi-value { font-size:1.6rem; font-weight:900; color:#1e293b; line-height:1.1; margin-top:.4rem; }
.kpi-label { font-size:.76rem; color:#64748b; margin-top:.15rem; font-weight:500; }
.kpi-badge { font-size:.68rem; font-weight:700; padding:.18rem .55rem; border-radius:20px; }
.badge-up   { background:#dcfce7; color:#15803d; }
.badge-down { background:#fee2e2; color:#dc2626; }
.badge-neu  { background:#f1f5f9; color:#64748b; }

/* ── Cards ── */
.card { background:#fff; border-radius:18px; box-shadow:0 1px 3px rgba(0,0,0,0.05),0 4px 20px rgba(0,0,0,0.04); overflow:hidden; }
.card-header { display:flex; align-items:center; justify-content:space-between; padding:1.1rem 1.35rem; border-bottom:1px solid #f1f5f9; }
.card-title { font-size:.875rem; font-weight:700; color:#1e293b; }
.card-subtitle { font-size:.7rem; color:#94a3b8; margin-top:.1rem; }
.card-body { padding:1.1rem 1.35rem; }

/* ── Table ── */
.erp-table { width:100%; border-collapse:collapse; font-size:.8rem; }
.erp-table thead tr { background:#f8fafc; }
.erp-table th { padding:.6rem .9rem; text-align:right; font-weight:600; color:#64748b; border-bottom:1px solid #e2e8f0; white-space:nowrap; font-size:.75rem; }
.erp-table td { padding:.7rem .9rem; border-bottom:1px solid #f8fafc; color:#334155; vertical-align:middle; }
.erp-table tbody tr:last-child td { border-bottom:none; }
.erp-table tbody tr { transition:background .15s; }
.erp-table tbody tr:hover td { background:#f8fafc; }

/* ── Badges ── */
.badge { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .65rem; border-radius:20px; font-size:.69rem; font-weight:600; white-space:nowrap; }
.b-green  { background:#dcfce7; color:#15803d; }
.b-red    { background:#fee2e2; color:#dc2626; }
.b-amber  { background:#fef3c7; color:#b45309; }
.b-blue   { background:#dbeafe; color:#1d4ed8; }
.b-gray   { background:#f1f5f9; color:#475569; }
.b-purple { background:#ede9fe; color:#6d28d9; }
.b-teal   { background:#ccfbf1; color:#0f766e; }

/* ── Alerts ── */
.alert-bar { display:flex; align-items:center; gap:.7rem; padding:.7rem 1.1rem; border-radius:13px; font-size:.8rem; font-weight:500; margin-bottom:.4rem; }
.a-danger  { background:#fff1f2; border:1px solid #fecdd3; color:#be123c; }
.a-warning { background:#fffbeb; border:1px solid #fde68a; color:#92400e; }

/* ── Progress ── */
.pbar { height:7px; border-radius:99px; background:#e2e8f0; overflow:hidden; margin-top:.3rem; }
.pfill { height:100%; border-radius:99px; transition:width .8s cubic-bezier(.4,0,.2,1); }

/* ── Timeline ── */
.tl-wrap { display:flex; gap:.65rem; padding:.5rem 0; }
.tl-icon-col { display:flex; flex-direction:column; align-items:center; }
.tl-icon { width:30px; height:30px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:.7rem; flex-shrink:0; }
.tl-line { width:1px; flex:1; background:#e2e8f0; margin:.2rem 0; min-height:12px; }

/* ── Sparkline strip ── */
.spark-strip { display:flex; align-items:flex-end; gap:2px; height:36px; }
.spark-bar { flex:1; border-radius:3px 3px 0 0; background:#146E6E; opacity:.3; transition:opacity .2s; min-width:3px; }
.spark-bar:last-child { opacity:1; }

/* ── Quick action btn ── */
.qa-btn { display:flex; flex-direction:column; align-items:center; gap:.4rem; padding:.9rem .5rem; border-radius:14px; background:#f8fafc; border:1.5px solid #e2e8f0; font-size:.72rem; font-weight:600; color:#475569; transition:all .18s; cursor:pointer; text-decoration:none; }
.qa-btn:hover { background:#146E6E; border-color:#146E6E; color:#fff; transform:translateY(-2px); }
.qa-btn:hover .qa-icon { background:rgba(255,255,255,.2); color:#fff; }
.qa-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:#e6f4f4; color:#146E6E; font-size:1rem; transition:all .18s; }

/* ── Animations ── */
@keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
.kpi-card { animation:fadeUp .4s ease both; }
.kpi-card:nth-child(1){animation-delay:.05s} .kpi-card:nth-child(2){animation-delay:.1s}
.kpi-card:nth-child(3){animation-delay:.15s} .kpi-card:nth-child(4){animation-delay:.2s}
.kpi-card:nth-child(5){animation-delay:.25s} .kpi-card:nth-child(6){animation-delay:.3s}

@media(max-width:768px){
    .kpi-value{font-size:1.3rem}
    .card-header{padding:.85rem 1rem} .card-body{padding:.85rem 1rem}
    .erp-table th,.erp-table td{padding:.5rem .65rem}
}
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- رأس الصفحة                                                      --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
        <h1 class="text-xl font-black text-slate-800 flex items-center gap-2">
            <span class="w-8 h-8 bg-primary rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa fa-gauge-high text-white text-sm"></i>
            </span>
            لوحة التحكم
        </h1>
        <p class="text-xs text-slate-400 mt-1 mr-10">
            <i class="fa fa-calendar-day ml-1 text-teal-500"></i>
            {{ now()->locale('ar')->isoFormat('dddd، D MMMM YYYY') }}
            &nbsp;·&nbsp;
            <i class="fa fa-clock ml-1"></i>
            <span id="liveClock">{{ now()->format('H:i') }}</span>
        </p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('invoices.create') }}"
           class="inline-flex items-center gap-2 bg-primary text-white text-xs font-bold px-4 py-2.5 rounded-xl hover:bg-primary-dark transition shadow-sm">
            <i class="fa fa-plus"></i> فاتورة جديدة
        </a>
        <a href="{{ route('purchase-orders.create') }}"
           class="inline-flex items-center gap-2 bg-white text-slate-600 text-xs font-bold px-4 py-2.5 rounded-xl hover:bg-slate-50 transition shadow-sm border border-slate-200">
            <i class="fa fa-cart-plus text-purple-500"></i> طلب شراء
        </a>
        <button onclick="window.print()"
                class="inline-flex items-center gap-2 bg-white text-slate-500 text-xs font-bold px-3 py-2.5 rounded-xl hover:bg-slate-50 transition shadow-sm border border-slate-200">
            <i class="fa fa-print"></i>
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- التنبيهات                                                        --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@if(count($alerts) > 0)
<div class="mb-4">
    @foreach($alerts as $alert)
    <div class="alert-bar {{ $alert['type'] === 'danger' ? 'a-danger' : 'a-warning' }}">
        <i class="fa fa-{{ $alert['icon'] }} flex-shrink-0"></i>
        <span class="flex-1">{{ $alert['message'] }}</span>
        <a href="{{ $alert['link'] }}" class="text-xs font-bold underline underline-offset-2 hover:no-underline flex-shrink-0">
            عرض ←
        </a>
    </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- بطاقات KPI                                                       --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 mb-4">

    {{-- مبيعات اليوم --}}
    <div class="kpi-card kpi-teal">
        <div class="flex items-start justify-between">
            <div class="kpi-icon"><i class="fa fa-arrow-trend-up"></i></div>
            <span class="kpi-badge badge-up"><i class="fa fa-arrow-up text-xs"></i> اليوم</span>
        </div>
        <p class="kpi-value">{{ number_format($salesToday,0) }}</p>
        <p class="kpi-label">مبيعات اليوم <span class="text-teal-500 font-semibold">ج.س</span></p>
    </div>

    {{-- المستحقات --}}
    <div class="kpi-card kpi-blue">
        <div class="flex items-start justify-between">
            <div class="kpi-icon"><i class="fa fa-hand-holding-dollar"></i></div>
            @if($overdueInvoices > 0)
            <span class="kpi-badge badge-down">{{ $overdueInvoices }} متأخر</span>
            @else
            <span class="kpi-badge badge-neu">جيد</span>
            @endif
        </div>
        <p class="kpi-value">{{ number_format($totalReceivables,0) }}</p>
        <p class="kpi-label">إجمالي المستحقات <span class="text-blue-500 font-semibold">ج.س</span></p>
    </div>

    {{-- المخزون الحرج --}}
    <div class="kpi-card kpi-amber">
        <div class="flex items-start justify-between">
            <div class="kpi-icon"><i class="fa fa-boxes-stacked"></i></div>
            @if($outOfStockCount > 0)
            <span class="kpi-badge badge-down">{{ $outOfStockCount }} نفد</span>
            @else
            <span class="kpi-badge badge-up">طبيعي</span>
            @endif
        </div>
        <p class="kpi-value">{{ $criticalStockCount + $outOfStockCount }}</p>
        <p class="kpi-label">منتج يحتاج انتباه</p>
    </div>

    {{-- الرواتب --}}
    <div class="kpi-card kpi-purple">
        <div class="flex items-start justify-between">
            <div class="kpi-icon"><i class="fa fa-money-check-dollar"></i></div>
            <span class="kpi-badge badge-neu">{{ now()->locale('ar')->isoFormat('MMM') }}</span>
        </div>
        <p class="kpi-value">{{ number_format($monthlyPayroll,0) }}</p>
        <p class="kpi-label">رواتب الشهر <span class="text-purple-500 font-semibold">ج.س</span></p>
    </div>

    {{-- العملاء --}}
    <div class="kpi-card kpi-green">
        <div class="flex items-start justify-between">
            <div class="kpi-icon"><i class="fa fa-users"></i></div>
            <span class="kpi-badge badge-up">نشط</span>
        </div>
        <p class="kpi-value">{{ \App\Models\Customer::count() }}</p>
        <p class="kpi-label">إجمالي العملاء</p>
    </div>

    {{-- المنتجات --}}
    <div class="kpi-card kpi-rose">
        <div class="flex items-start justify-between">
            <div class="kpi-icon"><i class="fa fa-tags"></i></div>
            <span class="kpi-badge badge-neu">مخزون</span>
        </div>
        <p class="kpi-value">{{ \App\Models\Product::where('quantity','>',0)->count() }}</p>
        <p class="kpi-label">منتج متوفر</p>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- إجراءات سريعة                                                    --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="card mb-4">
    <div class="card-header">
        <p class="card-title"><i class="fa fa-bolt text-amber-400 ml-1.5"></i> إجراءات سريعة</p>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-4 sm:grid-cols-8 gap-2">
            <a href="{{ route('invoices.create') }}" class="qa-btn">
                <div class="qa-icon"><i class="fa fa-file-invoice-dollar"></i></div>
                فاتورة جديدة
            </a>
            <a href="{{ route('purchase-orders.create') }}" class="qa-btn">
                <div class="qa-icon"><i class="fa fa-cart-shopping"></i></div>
                طلب شراء
            </a>
            <a href="{{ route('customers.create') }}" class="qa-btn">
                <div class="qa-icon"><i class="fa fa-user-plus"></i></div>
                عميل جديد
            </a>
            <a href="{{ route('products.create') }}" class="qa-btn">
                <div class="qa-icon"><i class="fa fa-box"></i></div>
                منتج جديد
            </a>
            <a href="{{ route('vouchers.create') }}" class="qa-btn">
                <div class="qa-icon"><i class="fa fa-file-invoice"></i></div>
                سند صرف
            </a>
            <a href="{{ route('journal.create') }}" class="qa-btn">
                <div class="qa-icon"><i class="fa fa-book-open"></i></div>
                قيد محاسبي
            </a>
            <a href="{{ route('employees.create') }}" class="qa-btn">
                <div class="qa-icon"><i class="fa fa-user-tie"></i></div>
                موظف جديد
            </a>
            <a href="{{ route('reports.index') }}" class="qa-btn">
                <div class="qa-icon"><i class="fa fa-chart-pie"></i></div>
                التقارير
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- الرسوم البيانية — صف أول                                         --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- مبيعات 12 شهر ── عمودان --}}
    <div class="card lg:col-span-2">
        <div class="card-header">
            <div>
                <p class="card-title">المبيعات الشهرية</p>
                <p class="card-subtitle">آخر 12 شهر · الجنيه السوداني</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="spark-strip" id="sparkStrip" style="opacity:.5"></div>
                <i class="fa fa-chart-area text-teal-400 text-lg"></i>
            </div>
        </div>
        <div class="card-body" style="padding-top:.5rem">
            <div style="height:230px;position:relative;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- دائرة حالة الفواتير --}}
    <div class="card flex flex-col">
        <div class="card-header">
            <div>
                <p class="card-title">حالة الفواتير</p>
                <p class="card-subtitle">آخر 10 فواتير</p>
            </div>
            <i class="fa fa-chart-pie text-blue-400 text-lg"></i>
        </div>
        <div class="card-body flex-1 flex flex-col items-center justify-center">
            <div style="position:relative;width:160px;height:160px;">
                <canvas id="donutChart"></canvas>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                    <span class="text-2xl font-black text-slate-800" id="donutTotal">—</span>
                    <span class="text-xs text-slate-400">فاتورة</span>
                </div>
            </div>
            <div class="w-full mt-4 space-y-1.5" id="donutLegend"></div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- الرسوم البيانية — صف ثانٍ                                        --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- مشتريات vs مبيعات --}}
    <div class="card">
        <div class="card-header">
            <div>
                <p class="card-title">المشتريات مقابل المبيعات</p>
                <p class="card-subtitle">آخر 6 أشهر</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span class="flex items-center gap-1"><span class="inline-block w-2.5 h-2.5 rounded-sm" style="background:#146E6E"></span>مبيعات</span>
                <span class="flex items-center gap-1"><span class="inline-block w-2.5 h-2.5 rounded-sm" style="background:#7c3aed"></span>مشتريات</span>
            </div>
        </div>
        <div class="card-body" style="padding-top:.5rem">
            <div style="height:210px;position:relative;">
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>

    {{-- أكثر المنتجات مبيعاً --}}
    <div class="card">
        <div class="card-header">
            <div>
                <p class="card-title"><i class="fa fa-fire text-orange-400 ml-1"></i> أكثر المنتجات مبيعاً</p>
                <p class="card-subtitle">آخر 30 يوماً</p>
            </div>
            <a href="{{ route('reports.index') }}" class="text-xs text-teal-600 font-semibold hover:underline">تفاصيل ←</a>
        </div>
        <div class="card-body">
            @php $maxQty = $topProducts->max('total_qty') ?: 1; @endphp
            @forelse($topProducts->take(7) as $i => $p)
            <div class="mb-2.5">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-slate-700 flex items-center gap-1.5 min-w-0">
                        <span class="inline-flex w-5 h-5 rounded-full text-xs font-black items-center justify-center flex-shrink-0
                            {{ $i===0?'bg-amber-400 text-white':($i===1?'bg-slate-300 text-slate-700':($i===2?'bg-orange-300 text-white':'bg-slate-100 text-slate-500')) }}">
                            {{ $i+1 }}
                        </span>
                        <span class="truncate">{{ $p->name_ar }}</span>
                    </span>
                    <span class="text-xs font-bold text-teal-700 mr-2 flex-shrink-0">{{ number_format($p->total_qty) }}</span>
                </div>
                @php
                    $pct = round(($p->total_qty/$maxQty)*100);
                    $clr = ['#146E6E','#1db8b8','#3b82f6','#7c3aed','#e11d48','#f59e0b','#059669'][$i%7];
                @endphp
                <div class="pbar"><div class="pfill" style="width:{{ $pct }}%;background:{{ $clr }}"></div></div>
            </div>
            @empty
            <div class="text-center py-8 text-slate-400 text-sm">
                <i class="fa fa-box-open text-3xl block mb-2"></i> لا توجد بيانات
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- الجداول — صف                                                      --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- آخر الفواتير --}}
    <div class="card">
        <div class="card-header">
            <p class="card-title"><i class="fa fa-file-invoice text-teal-500 ml-1.5"></i> آخر الفواتير</p>
            <a href="{{ route('invoices.index') }}" class="text-xs text-teal-600 font-bold hover:underline">الكل ←</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="erp-table">
                <thead><tr>
                    <th>الرقم</th><th>العميل</th><th class="text-left">المبلغ</th><th>الحالة</th>
                </tr></thead>
                <tbody>
                @forelse($latestInvoices as $inv)
                @php
                    $sm = ['confirmed'=>['b-green','مؤكدة','circle-check'],'draft'=>['b-gray','مسودة','pencil'],
                           'cancelled'=>['b-red','ملغية','xmark'],'partial'=>['b-amber','جزئية','clock'],
                           'paid'=>['b-blue','مسددة','check-double']];
                    $s = $sm[$inv->status] ?? ['b-gray',$inv->status,'question'];
                @endphp
                <tr>
                    <td><a href="{{ route('invoices.show',$inv) }}" class="font-bold text-teal-700 hover:underline">#{{ $inv->invoice_number ?? $inv->id }}</a></td>
                    <td class="text-slate-500 max-w-[120px] truncate">{{ $inv->customer?->name ?? '—' }}</td>
                    <td class="font-bold text-slate-700 text-left">{{ number_format($inv->total,0) }}</td>
                    <td><span class="badge {{ $s[0] }}"><i class="fa fa-{{ $s[2] }}"></i> {{ $s[1] }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-6 text-slate-400 text-xs">لا توجد فواتير</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- آخر طلبات الشراء --}}
    <div class="card">
        <div class="card-header">
            <p class="card-title"><i class="fa fa-cart-shopping text-purple-500 ml-1.5"></i> طلبات الشراء</p>
            <a href="{{ route('purchase-orders.index') }}" class="text-xs text-purple-600 font-bold hover:underline">الكل ←</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="erp-table">
                <thead><tr>
                    <th>الرقم</th><th>المورد</th><th class="text-left">الإجمالي</th><th>الحالة</th>
                </tr></thead>
                <tbody>
                @forelse($latestPurchaseOrders as $po)
                @php
                    $pm = ['pending'=>['b-amber','معلق','clock'],'approved'=>['b-blue','معتمد','thumbs-up'],
                           'received'=>['b-green','مستلم','check'],'cancelled'=>['b-red','ملغي','xmark'],
                           'partial'=>['b-purple','جزئي','minus']];
                    $ps = $pm[$po->status] ?? ['b-gray',$po->status,'question'];
                @endphp
                <tr>
                    <td><a href="{{ route('purchase-orders.show',$po) }}" class="font-bold text-purple-700 hover:underline">#{{ $po->po_number ?? $po->id }}</a></td>
                    <td class="text-slate-500 max-w-[120px] truncate">{{ $po->supplier?->name ?? '—' }}</td>
                    <td class="font-bold text-slate-700 text-left">{{ number_format($po->total??0,0) }}</td>
                    <td><span class="badge {{ $ps[0] }}"><i class="fa fa-{{ $ps[2] }}"></i> {{ $ps[1] }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-6 text-slate-400 text-xs">لا توجد طلبات</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- المخزون الحرج + النشاطات                                         --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- المخزون الحرج --}}
    <div class="card lg:col-span-2">
        <div class="card-header">
            <p class="card-title"><i class="fa fa-triangle-exclamation text-amber-500 ml-1.5"></i> المخزون الحرج</p>
            <a href="{{ route('products.index') }}?filter=critical" class="text-xs text-amber-600 font-bold hover:underline">إدارة المخزون ←</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="erp-table">
                <thead><tr>
                    <th>المنتج</th><th>الكمية</th><th>حد الطلب</th><th>الحالة</th><th style="min-width:100px;">المستوى</th>
                </tr></thead>
                <tbody>
                @php
                    $critProds = \App\Models\Product::whereRaw('quantity <= reorder_point')
                        ->orderBy('quantity')->limit(8)->get();
                @endphp
                @forelse($critProds as $prod)
                @php
                    $lvl = $prod->reorder_point > 0 ? min(100, round(($prod->quantity / $prod->reorder_point)*100)) : 0;
                    $barC = $lvl==0 ? '#ef4444' : ($lvl<40 ? '#f59e0b' : '#22c55e');
                @endphp
                <tr>
                    <td class="font-medium text-slate-700">{{ $prod->name_ar }}</td>
                    <td class="font-black {{ $prod->quantity==0?'text-red-600':'text-amber-600' }}">{{ $prod->quantity }}</td>
                    <td class="text-slate-400">{{ $prod->reorder_point }}</td>
                    <td>
                        @if($prod->quantity==0)
                        <span class="badge b-red"><i class="fa fa-xmark"></i> نفد</span>
                        @else
                        <span class="badge b-amber"><i class="fa fa-arrow-down"></i> حرج</span>
                        @endif
                    </td>
                    <td>
                        <div class="pbar"><div class="pfill" style="width:{{ $lvl }}%;background:{{ $barC }}"></div></div>
                        <span class="text-xs text-slate-400">{{ $lvl }}%</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-400 text-sm">
                        <i class="fa fa-circle-check text-green-400 text-2xl block mb-1"></i>
                        جميع المنتجات بمستوى جيد
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- آخر النشاطات --}}
    <div class="card">
        <div class="card-header">
            <p class="card-title"><i class="fa fa-clock-rotate-left text-blue-400 ml-1.5"></i> سجل النشاط</p>
        </div>
        <div class="card-body" style="max-height:310px;overflow-y:auto;padding-top:.6rem;">
            @php
                $acts = \App\Models\ActivityLog::with('user')->latest()->limit(12)->get();
                $actCfg = [
                    'create' =>['bg-green-100 text-green-600','plus'],
                    'update' =>['bg-blue-100 text-blue-600','pen'],
                    'delete' =>['bg-red-100 text-red-500','trash'],
                    'login'  =>['bg-teal-100 text-teal-600','right-to-bracket'],
                    'logout' =>['bg-gray-100 text-gray-400','right-from-bracket'],
                    'approve'=>['bg-purple-100 text-purple-600','check'],
                ];
            @endphp
            @forelse($acts as $act)
            <div class="tl-wrap">
                <div class="tl-icon-col">
                    @php $ac = $actCfg[$act->action] ?? ['bg-gray-100 text-gray-400','circle']; @endphp
                    <div class="tl-icon {{ $ac[0] }}"><i class="fa fa-{{ $ac[1] }}"></i></div>
                    @if(!$loop->last)<div class="tl-line"></div>@endif
                </div>
                <div class="flex-1 min-w-0 pb-1">
                    <p class="text-xs text-slate-700 leading-snug">{{ $act->description }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-xs text-slate-400">{{ $act->user?->name ?? 'النظام' }}</span>
                        <span class="text-slate-200">·</span>
                        <span class="text-xs text-slate-300">{{ $act->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-slate-400 text-xs">
                <i class="fa fa-list text-xl block mb-1"></i> لا توجد نشاطات
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- إحصائيات سريعة — شريط سفلي                                      --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@php
$qstats = [
    ['label'=>'فواتير هذا الشهر',   'val'=>\App\Models\Invoice::whereMonth('created_at',now()->month)->count(),              'icon'=>'file-invoice',        'cl'=>'text-teal-600',   'bg'=>'bg-teal-50'],
    ['label'=>'طلبات شراء معلقة',   'val'=>\App\Models\PurchaseOrder::where('status','pending')->count(),                    'icon'=>'cart-shopping',       'cl'=>'text-amber-600',  'bg'=>'bg-amber-50'],
    ['label'=>'موردون نشطون',        'val'=>\App\Models\Supplier::count(),                                                    'icon'=>'truck',               'cl'=>'text-purple-600', 'bg'=>'bg-purple-50'],
    ['label'=>'فواتير متأخرة',       'val'=>\App\Models\Invoice::where('status','confirmed')->where('type','credit')->where('due_date','<',today())->count(), 'icon'=>'triangle-exclamation','cl'=>'text-rose-600','bg'=>'bg-rose-50'],
    ['label'=>'حركات مخزون اليوم',  'val'=>\App\Models\StockMovement::whereDate('created_at',today())->count(),              'icon'=>'boxes-stacked',       'cl'=>'text-blue-600',   'bg'=>'bg-blue-50'],
    ['label'=>'موظفون نشطون',        'val'=>\App\Models\Employee::where('status','active')->count(),                          'icon'=>'user-tie',            'cl'=>'text-green-600',  'bg'=>'bg-green-50'],
];
@endphp
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    @foreach($qstats as $qs)
    <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 flex items-center gap-2.5 hover:shadow-md transition">
        <div class="w-9 h-9 {{ $qs['bg'] }} rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa fa-{{ $qs['icon'] }} {{ $qs['cl'] }} text-sm"></i>
        </div>
        <div class="min-w-0">
            <p class="text-lg font-black text-slate-800 leading-tight">{{ $qs['val'] }}</p>
            <p class="text-xs text-slate-400 truncate leading-tight">{{ $qs['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
Chart.defaults.font.family = "'Cairo','Tajawal',sans-serif";
Chart.defaults.color = '#64748b';

const salesData    = @json($monthlySales);
const topProducts  = @json($topProducts);

// ── ساعة حية ─────────────────────────────────────────────────────
(function(){
    function tick(){
        const n = new Date();
        const t = n.getHours().toString().padStart(2,'0')+':'+n.getMinutes().toString().padStart(2,'0');
        const el = document.getElementById('liveClock');
        if(el) el.textContent = t;
    }
    tick(); setInterval(tick, 30000);
})();

// ── Spark strip ───────────────────────────────────────────────────
(function(){
    const strip = document.getElementById('sparkStrip');
    if(!strip || !salesData.length) return;
    const vals = salesData.slice(-8).map(d=>d.total);
    const mx = Math.max(...vals)||1;
    vals.forEach((v,i)=>{
        const b = document.createElement('div');
        b.className = 'spark-bar';
        b.style.height = Math.max(6,Math.round((v/mx)*36))+'px';
        if(i===vals.length-1) b.style.opacity='1';
        strip.appendChild(b);
    });
    strip.style.opacity='.8';
})();

// ── 1. مخطط المبيعات (Area) ───────────────────────────────────────
(function(){
    const ctx = document.getElementById('salesChart');
    if(!ctx) return;
    const labels = salesData.map(d=>d.label);
    const values = salesData.map(d=>d.total);
    const g = ctx.getContext('2d').createLinearGradient(0,0,0,230);
    g.addColorStop(0,'rgba(20,110,110,.28)');
    g.addColorStop(1,'rgba(20,110,110,.00)');
    new Chart(ctx,{
        type:'line',
        data:{
            labels,
            datasets:[{
                label:'المبيعات',data:values,
                borderColor:'#146E6E',backgroundColor:g,
                borderWidth:2.5,pointBackgroundColor:'#146E6E',
                pointRadius:3,pointHoverRadius:6,
                fill:true,tension:.42
            }]
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            plugins:{
                legend:{display:false},
                tooltip:{rtl:true,callbacks:{label:c=>' '+c.parsed.y.toLocaleString()+' ج.س'}}
            },
            scales:{
                x:{grid:{display:false},ticks:{maxTicksLimit:7,font:{size:10}}},
                y:{grid:{color:'#f1f5f9'},border:{dash:[4,4]},
                   ticks:{font:{size:10},callback:v=>v>=1000?(v/1000).toFixed(0)+'k':v}}
            }
        }
    });
})();

// ── 2. مخطط دائري حالة الفواتير ──────────────────────────────────
(function(){
    const ctx = document.getElementById('donutChart');
    if(!ctx) return;
    const invs = @json($latestInvoices);
    const cm = {};
    invs.forEach(i=>{ cm[i.status]=(cm[i.status]||0)+1; });
    const arMap = {confirmed:'مؤكدة',draft:'مسودة',cancelled:'ملغية',partial:'جزئية',paid:'مسددة'};
    const clMap = {confirmed:'#146E6E',draft:'#94a3b8',cancelled:'#ef4444',partial:'#f59e0b',paid:'#3b82f6'};
    const keys  = Object.keys(cm);
    const vals  = Object.values(cm);
    const total = vals.reduce((a,b)=>a+b,0);
    document.getElementById('donutTotal').textContent = total;
    new Chart(ctx,{
        type:'doughnut',
        data:{
            labels:keys.map(k=>arMap[k]||k),
            datasets:[{data:vals,backgroundColor:keys.map(k=>clMap[k]||'#cbd5e1'),borderWidth:2,borderColor:'#fff',hoverOffset:5}]
        },
        options:{
            responsive:true,maintainAspectRatio:false,cutout:'72%',
            plugins:{legend:{display:false},tooltip:{rtl:true}}
        }
    });
    const leg = document.getElementById('donutLegend');
    keys.forEach((k,i)=>{
        const pct = total?Math.round(vals[i]/total*100):0;
        leg.innerHTML+=`<div class="flex items-center justify-between text-xs">
            <span class="flex items-center gap-1.5 text-slate-600">
                <span class="w-2 h-2 rounded-sm flex-shrink-0" style="background:${clMap[k]||'#cbd5e1'}"></span>${arMap[k]||k}
            </span>
            <span class="font-bold text-slate-700">${vals[i]} <span class="text-slate-400 font-normal">(${pct}%)</span></span>
        </div>`;
    });
})();

// ── 3. مخطط أعمدة مشتريات vs مبيعات ─────────────────────────────
(function(){
    const ctx = document.getElementById('barChart');
    if(!ctx) return;
    const purchData = @json(
        \App\Models\PurchaseOrder::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total) as total')
            ->where('created_at','>=',now()->subMonths(6)->startOfMonth())
            ->groupBy('year','month')->orderBy('year')->orderBy('month')
            ->get()->map(fn($r)=>['label'=>$r->month.'/'.$r->year,'total'=>(float)$r->total])
    );
    const sl6 = salesData.slice(-6);
    const pl6 = purchData.slice(-6);
    const labels = sl6.map(d=>d.label);
    new Chart(ctx,{
        type:'bar',
        data:{
            labels,
            datasets:[
                {label:'المبيعات',data:sl6.map(d=>d.total),backgroundColor:'rgba(20,110,110,.8)',borderRadius:6,borderSkipped:false},
                {label:'المشتريات',data:pl6.map(d=>d.total),backgroundColor:'rgba(124,58,237,.7)',borderRadius:6,borderSkipped:false}
            ]
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            plugins:{
                legend:{display:false},
                tooltip:{rtl:true,callbacks:{label:c=>' '+c.parsed.y.toLocaleString()+' ج.س'}}
            },
            scales:{
                x:{grid:{display:false},ticks:{font:{size:10}}},
                y:{grid:{color:'#f1f5f9'},border:{dash:[4,4]},
                   ticks:{font:{size:10},callback:v=>v>=1000?(v/1000).toFixed(0)+'k':v}}
            }
        }
    });
})();
</script>
@endpush
