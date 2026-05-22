@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@section('content')

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- التنبيهات                                                    --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if(count($alerts) > 0)
<div class="space-y-2 mb-6">
    @foreach($alerts as $alert)
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm
        {{ $alert['type'] === 'danger' ? 'bg-red-50 border border-red-200 text-red-800' : 'bg-yellow-50 border border-yellow-200 text-yellow-800' }}">
        <i class="fa fa-{{ $alert['icon'] }}"></i>
        <span class="flex-1">{{ $alert['message'] }}</span>
        <a href="{{ $alert['link'] }}" class="underline hover:no-underline text-xs font-medium">عرض</a>
    </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- البطاقات الإحصائية                                          --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- مبيعات اليوم --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-teal-50 rounded-xl flex items-center justify-center">
                <i class="fa fa-sack-dollar text-teal-600 text-xl"></i>
            </div>
            <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full font-medium">اليوم</span>
        </div>
        <p class="text-2xl font-black text-gray-800">{{ number_format($salesToday, 0) }}</p>
        <p class="text-sm text-gray-500 mt-1">مبيعات اليوم (ج.س)</p>
    </div>

    {{-- المخزون الحرج --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center">
                <i class="fa fa-boxes-stacked text-orange-500 text-xl"></i>
            </div>
            @if($criticalStockCount > 0 || $outOfStockCount > 0)
            <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded-full font-medium">تنبيه</span>
            @endif
        </div>
        <p class="text-2xl font-black text-gray-800">{{ $outOfStockCount + $criticalStockCount }}</p>
        <p class="text-sm text-gray-500 mt-1">منتج يحتاج انتباه</p>
    </div>

    {{-- المستحقات --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                <i class="fa fa-hand-holding-dollar text-blue-600 text-xl"></i>
            </div>
            @if($overdueInvoices > 0)
            <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded-full font-medium">{{ $overdueInvoices }} متأخر</span>
            @endif
        </div>
        <p class="text-2xl font-black text-gray-800">{{ number_format($totalReceivables, 0) }}</p>
        <p class="text-sm text-gray-500 mt-1">إجمالي المستحقات (ج.س)</p>
    </div>

    {{-- الرواتب --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                <i class="fa fa-money-check-dollar text-purple-600 text-xl"></i>
            </div>
            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full font-medium">{{ now()->format('M Y') }}</span>
        </div>
        <p class="text-2xl font-black text-gray-800">{{ number_format($monthlyPayroll, 0) }}</p>
        <p class="text-sm text-gray-500 mt-1">رواتب الشهر (ج.س)</p>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- الرسوم البيانية                                              --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- مبيعات آخر 12 شهر --}}
    <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-700 text-sm">المبيعات — آخر 12 شهر</h3>
            <i class="fa fa-chart-line text-primary text-lg"></i>
        </div>
        <div class="h-64">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    {{-- أكثر المنتجات مبيعاً --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-700 text-sm">أكثر المنتجات مبيعاً</h3>
            <i class="fa fa-chart-bar text-primary text-lg"></i>
        </div>
        <div class="h-64">
            <canvas id="productsChart"></canvas>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- آخر الفواتير وآخر المشتريات                                  --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- آخر 10 فواتير --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-bold text-gray-700 text-sm">آخر الفواتير</h3>
            <a href="{{ route('invoices.index') }}" class="text-xs text-primary hover:underline">عرض الكل</a>
        </div>
        <div class="divide-y">
            @forelse($latestInvoices as $invoice)
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $invoice->customer?->name ?? 'عميل نقدي' }}</p>
                    <p class="text-xs text-gray-400">{{ $invoice->invoice_number }} • {{ $invoice->created_at->diffForHumans() }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">{{ number_format($invoice->total, 0) }} ج.س</p>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        @switch($invoice->status)
                            @case('paid')    bg-green-100 text-green-700 @break
                            @case('partial') bg-yellow-100 text-yellow-700 @break
                            @case('cancelled') bg-red-100 text-red-700 @break
                            @default         bg-blue-100 text-blue-700
                        @endswitch
                    ">
                        @switch($invoice->status)
                            @case('paid')      مسدد @break
                            @case('partial')   جزئي @break
                            @case('cancelled') ملغي @break
                            @default           معلق
                        @endswitch
                    </span>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">
                <i class="fa fa-receipt text-3xl mb-2 block"></i>
                لا توجد فواتير بعد
            </div>
            @endforelse
        </div>
    </div>

    {{-- آخر 10 طلبات مشتريات --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-bold text-gray-700 text-sm">آخر طلبات الشراء</h3>
            <a href="{{ route('purchase-orders.index') }}" class="text-xs text-primary hover:underline">عرض الكل</a>
        </div>
        <div class="divide-y">
            @forelse($latestPurchaseOrders as $po)
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $po->supplier?->name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $po->order_number ?? '#' . $po->id }} • {{ $po->created_at->diffForHumans() }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">{{ number_format($po->total ?? 0, 0) }} ج.س</p>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium">
                        @switch($po->status ?? 'pending')
                            @case('received')  تم الاستلام @break
                            @case('approved')  معتمد @break
                            @case('cancelled') ملغي @break
                            @default           معلق
                        @endswitch
                    </span>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">
                <i class="fa fa-truck text-3xl mb-2 block"></i>
                لا توجد طلبات شراء بعد
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
// ─── بيانات المبيعات ──────────────────────────────────────────────────
const salesData = @json($monthlySales);
const topProductsData = @json($topProducts);

// ─── رسم بياني: المبيعات ─────────────────────────────────────────────
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: salesData.map(d => d.label),
        datasets: [{
            label: 'المبيعات',
            data: salesData.map(d => d.total),
            borderColor: '#00838F',
            backgroundColor: 'rgba(0,131,143,0.1)',
            borderWidth: 2.5,
            pointBackgroundColor: '#00838F',
            pointRadius: 4,
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { font: { family: 'Tajawal' }, callback: v => v.toLocaleString('ar') }
            },
            x: {
                grid: { display: false },
                ticks: { font: { family: 'Tajawal', size: 11 } }
            }
        }
    }
});

// ─── رسم بياني: المنتجات ─────────────────────────────────────────────
const prodCtx = document.getElementById('productsChart').getContext('2d');
new Chart(prodCtx, {
    type: 'bar',
    data: {
        labels: topProductsData.map(d => d.name_ar),
        datasets: [{
            label: 'الكمية المباعة',
            data: topProductsData.map(d => d.total_qty),
            backgroundColor: [
                '#00838F','#00ACC1','#00BCD4','#26C6DA',
                '#4DD0E1','#80DEEA','#B2EBF2','#E0F7FA'
            ],
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { family: 'Tajawal' } } },
            y: { grid: { display: false }, ticks: { font: { family: 'Tajawal', size: 11 } } }
        }
    }
});
</script>
@endpush
