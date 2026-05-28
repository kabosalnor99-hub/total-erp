{{-- المسار: resources/views/reports/sales/by-customer.blade.php --}}

@extends('layouts.app')

@section('title', 'تقرير المبيعات حسب العميل')

@section('content')
<div class="space-y-6">

    {{-- رأس الصفحة --}}
    <div class="bg-gradient-to-r from-teal-600 to-teal-700 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('reports.index') }}"
                   class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold">تقرير المبيعات حسب العميل</h1>
                    <p class="text-teal-100 text-sm mt-1">
                        من {{ \Carbon\Carbon::parse(request('date_from', now()->startOfMonth()))->format('Y-m-d') }}
                        إلى {{ \Carbon\Carbon::parse(request('date_to', now()))->format('Y-m-d') }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('reports.export-pdf', 'sales-by-customer') }}?{{ http_build_query(request()->all()) }}"
                   class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ route('reports.export-excel', 'sales-by-customer') }}?{{ http_build_query(request()->all()) }}"
                   class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </div>
        </div>
    </div>

    {{-- فلتر التواريخ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" action="{{ route('reports.sales-by-customer') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
                <input type="date" name="date_from"
                       value="{{ request('date_from', now()->startOfMonth()->toDateString()) }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to"
                       value="{{ request('date_to', now()->toDateString()) }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm">
            </div>
            <button type="submit"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <i class="fas fa-search"></i> عرض
            </button>
            <a href="{{ route('reports.sales-by-customer') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <i class="fas fa-redo"></i> إعادة تعيين
            </a>
        </form>
    </div>

    {{-- بطاقات الملخص --}}
    @php
        $totalInvoices  = collect($data)->sum('total_invoices');
        $totalAmount    = collect($data)->sum('total_amount');
        $totalBalance   = collect($data)->sum('balance');
        $totalPaid      = $totalAmount - $totalBalance;
        $customersCount = collect($data)->count();
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl p-5 text-white shadow-lg">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-lg"></i>
                </div>
                <span class="text-teal-100 text-sm font-medium">عدد العملاء</span>
            </div>
            <div class="text-3xl font-black">{{ number_format($customersCount) }}</div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white shadow-lg">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-invoice text-lg"></i>
                </div>
                <span class="text-blue-100 text-sm font-medium">إجمالي الفواتير</span>
            </div>
            <div class="text-3xl font-black">{{ number_format($totalInvoices) }}</div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-5 text-white shadow-lg">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-lg"></i>
                </div>
                <span class="text-green-100 text-sm font-medium">إجمالي المبيعات</span>
            </div>
            <div class="text-2xl font-black">{{ number_format($totalAmount, 2) }}</div>
            <div class="text-green-200 text-xs mt-1">ج.س</div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-5 text-white shadow-lg">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-lg"></i>
                </div>
                <span class="text-red-100 text-sm font-medium">إجمالي المديونيات</span>
            </div>
            <div class="text-2xl font-black">{{ number_format($totalBalance, 2) }}</div>
            <div class="text-red-200 text-xs mt-1">ج.س</div>
        </div>
    </div>

    {{-- الجدول --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-table text-teal-600"></i>
                تفاصيل المبيعات حسب العميل
            </h2>
            <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                {{ $customersCount }} عميل
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-right text-xs font-semibold text-gray-500 px-6 py-4 rounded-tr-lg">#</th>
                        <th class="text-right text-xs font-semibold text-gray-500 px-6 py-4">العميل</th>
                        <th class="text-right text-xs font-semibold text-gray-500 px-6 py-4">الهاتف</th>
                        <th class="text-center text-xs font-semibold text-gray-500 px-6 py-4">عدد الفواتير</th>
                        <th class="text-right text-xs font-semibold text-gray-500 px-6 py-4">إجمالي المبيعات</th>
                        <th class="text-right text-xs font-semibold text-gray-500 px-6 py-4">المدفوع</th>
                        <th class="text-right text-xs font-semibold text-gray-500 px-6 py-4">المديونية</th>
                        <th class="text-center text-xs font-semibold text-gray-500 px-6 py-4 rounded-tl-lg">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $index => $row)
                    @php
                        $paid       = ($row['total_amount'] ?? 0) - ($row['balance'] ?? 0);
                        $paidPct    = $row['total_amount'] > 0 ? ($paid / $row['total_amount']) * 100 : 100;
                        $hasBalance = ($row['balance'] ?? 0) > 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-400">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            @if(isset($row['customer']) && $row['customer'])
                            <a href="{{ route('customers.show', $row['customer_id']) }}"
                               class="font-semibold text-gray-800 hover:text-teal-600 transition">
                                {{ $row['customer']->name ?? 'عميل #' . $row['customer_id'] }}
                            </a>
                            @else
                            <span class="font-semibold text-gray-800">
                                {{ $row['customer_name'] ?? 'عميل #' . $row['customer_id'] }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                            {{ $row['customer']->phone ?? $row['phone'] ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">
                                {{ number_format($row['total_invoices']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-800 text-right font-mono">
                            {{ number_format($row['total_amount'] ?? 0, 2) }}
                            <span class="text-gray-400 font-normal text-xs">ج.س</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-semibold text-green-600 font-mono">
                                {{ number_format($paid, 2) }}
                                <span class="text-gray-400 font-normal text-xs">ج.س</span>
                            </span>
                            {{-- شريط التقدم --}}
                            <div class="mt-1 h-1.5 bg-gray-200 rounded-full overflow-hidden w-24">
                                <div class="h-full bg-green-500 rounded-full transition-all"
                                     style="width: {{ min(100, $paidPct) }}%"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($hasBalance)
                            <span class="text-sm font-bold text-red-600 font-mono">
                                {{ number_format($row['balance'], 2) }}
                                <span class="text-red-400 font-normal text-xs">ج.س</span>
                            </span>
                            @else
                            <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if(!$hasBalance)
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">
                                مسدد
                            </span>
                            @elseif($paidPct >= 50)
                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">
                                جزئي
                            </span>
                            @else
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">
                                آجل
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="text-gray-300 text-5xl mb-4">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <p class="text-gray-400 font-medium">لا توجد بيانات مبيعات في هذه الفترة</p>
                            <p class="text-gray-300 text-sm mt-1">جرب تغيير نطاق التواريخ</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($data) > 0)
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-200">
                        <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-700">
                            الإجمالي
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">
                                {{ number_format($totalInvoices) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-black text-gray-800 font-mono">
                                {{ number_format($totalAmount, 2) }}
                                <span class="text-gray-400 font-normal text-xs">ج.س</span>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-black text-green-600 font-mono">
                                {{ number_format($totalPaid, 2) }}
                                <span class="text-gray-400 font-normal text-xs">ج.س</span>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-black text-red-600 font-mono">
                                {{ number_format($totalBalance, 2) }}
                                <span class="text-gray-400 font-normal text-xs">ج.س</span>
                            </span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
