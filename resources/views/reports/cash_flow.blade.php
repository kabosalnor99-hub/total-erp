{{-- المسار الكامل: resources/views/reports/cash_flow.blade.php --}}
@extends('layouts.app')

@section('title', 'تقرير التدفقات النقدية')
@section('page-title', 'تقرير التدفقات النقدية')

@section('breadcrumb')
    <span class="text-gray-400 mx-1">/</span>
    <a href="{{ route('reports.index') }}" class="hover:text-primary">التقارير</a>
    <span class="text-gray-400 mx-1">/</span>
    <span class="text-gray-600">التدفقات النقدية</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- ─── فلتر الفترة ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <form method="GET" action="{{ route('reports.cash-flow') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">من تاريخ</label>
                <input type="date" name="date_from"
                       value="{{ request('date_from', now()->startOfMonth()->toDateString()) }}"
                       class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to"
                       value="{{ request('date_to', now()->toDateString()) }}"
                       class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <button type="submit"
                    class="bg-primary text-white px-6 py-2 rounded-xl text-sm font-semibold hover:bg-primary-dark transition">
                <i class="fa fa-filter ml-1"></i> تصفية
            </button>
            <a href="{{ route('reports.cash-flow') }}"
               class="border border-gray-200 text-gray-600 px-4 py-2 rounded-xl text-sm hover:bg-gray-50 transition">
                إعادة تعيين
            </a>
        </form>
    </div>

    {{-- ─── بطاقة الملخص الرئيسي ────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- إجمالي المقبوضات --}}
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 rounded-2xl p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa fa-arrow-down text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-emerald-700 font-semibold mb-1">إجمالي المقبوضات</p>
                <p class="text-xl font-black text-emerald-800 dir-ltr text-right">
                    {{ number_format($data['operating']['sales_receipts'], 2) }}
                    <span class="text-sm font-normal">ج.س</span>
                </p>
            </div>
        </div>

        {{-- إجمالي المدفوعات --}}
        <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-2xl p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa fa-arrow-up text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-red-700 font-semibold mb-1">إجمالي المدفوعات</p>
                <p class="text-xl font-black text-red-800 dir-ltr text-right">
                    {{ number_format($data['operating']['purchase_payments'] + $data['operating']['payroll_payments'], 2) }}
                    <span class="text-sm font-normal">ج.س</span>
                </p>
            </div>
        </div>

        {{-- صافي التدفق النقدي --}}
        @php $netPositive = $data['net_cash_flow'] >= 0; @endphp
        <div class="bg-gradient-to-br {{ $netPositive ? 'from-primary/10 to-primary/20 border-primary/30' : 'from-orange-50 to-orange-100 border-orange-200' }} border rounded-2xl p-5 flex items-center gap-4">
            <div class="w-12 h-12 {{ $netPositive ? 'bg-primary' : 'bg-orange-500' }} rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa fa-scale-balanced text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs {{ $netPositive ? 'text-primary' : 'text-orange-700' }} font-semibold mb-1">صافي التدفق النقدي</p>
                <p class="text-xl font-black {{ $netPositive ? 'text-primary' : 'text-orange-800' }} dir-ltr text-right">
                    {{ $netPositive ? '+' : '' }}{{ number_format($data['net_cash_flow'], 2) }}
                    <span class="text-sm font-normal">ج.س</span>
                </p>
            </div>
        </div>
    </div>

    {{-- ─── تفاصيل التدفقات التشغيلية ────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- العنوان --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fa fa-money-bill-wave text-primary text-sm"></i>
                </div>
                <h2 class="font-bold text-gray-800">التدفقات النقدية التشغيلية</h2>
            </div>
            <span class="text-xs text-gray-500 bg-white border border-gray-200 px-3 py-1 rounded-full">
                {{ \Carbon\Carbon::parse($data['date_from'])->format('d/m/Y') }}
                —
                {{ \Carbon\Carbon::parse($data['date_to'])->format('d/m/Y') }}
            </span>
        </div>

        {{-- الجدول --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <th class="text-right px-6 py-3 font-semibold">البند</th>
                        <th class="text-right px-6 py-3 font-semibold">النوع</th>
                        <th class="text-left px-6 py-3 font-semibold">المبلغ (ج.س)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    {{-- مقبوضات المبيعات --}}
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-emerald-400 rounded-full"></div>
                                <span class="font-medium text-gray-800">مقبوضات المبيعات</span>
                            </div>
                            <p class="text-xs text-gray-400 mr-4 mt-0.5">سندات القبض خلال الفترة</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-emerald-100 text-emerald-700 text-xs font-semibold px-2 py-1 rounded-full">تدفق داخل</span>
                        </td>
                        <td class="px-6 py-4 text-left font-bold text-emerald-600 dir-ltr">
                            + {{ number_format($data['operating']['sales_receipts'], 2) }}
                        </td>
                    </tr>

                    {{-- مدفوعات المشتريات --}}
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                                <span class="font-medium text-gray-800">مدفوعات المشتريات</span>
                            </div>
                            <p class="text-xs text-gray-400 mr-4 mt-0.5">سندات الصرف للموردين</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded-full">تدفق خارج</span>
                        </td>
                        <td class="px-6 py-4 text-left font-bold text-red-600 dir-ltr">
                            − {{ number_format($data['operating']['purchase_payments'], 2) }}
                        </td>
                    </tr>

                    {{-- مدفوعات الرواتب --}}
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-orange-400 rounded-full"></div>
                                <span class="font-medium text-gray-800">مدفوعات الرواتب</span>
                            </div>
                            <p class="text-xs text-gray-400 mr-4 mt-0.5">رواتب الموظفين المصروفة</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-2 py-1 rounded-full">تدفق خارج</span>
                        </td>
                        <td class="px-6 py-4 text-left font-bold text-orange-600 dir-ltr">
                            − {{ number_format($data['operating']['payroll_payments'], 2) }}
                        </td>
                    </tr>

                </tbody>

                {{-- صافي التدفق التشغيلي --}}
                <tfoot>
                    <tr class="{{ $netPositive ? 'bg-emerald-50' : 'bg-red-50' }}">
                        <td class="px-6 py-4" colspan="2">
                            <span class="font-black text-gray-800 text-base">صافي التدفق النقدي التشغيلي</span>
                        </td>
                        <td class="px-6 py-4 text-left font-black text-lg dir-ltr {{ $netPositive ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ $netPositive ? '+' : '' }}{{ number_format($data['operating']['net'], 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ─── رسم بياني بسيط ────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-800 mb-5 flex items-center gap-2">
            <i class="fa fa-chart-bar text-primary"></i>
            مقارنة التدفقات
        </h3>
        @php
            $inflow  = $data['operating']['sales_receipts'];
            $outflow = $data['operating']['purchase_payments'] + $data['operating']['payroll_payments'];
            $max     = max($inflow, $outflow, 1);
            $inPct   = round(($inflow  / $max) * 100);
            $outPct  = round(($outflow / $max) * 100);
        @endphp
        <div class="space-y-4">
            {{-- المقبوضات --}}
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-semibold text-gray-700">المقبوضات</span>
                    <span class="font-bold text-emerald-600 dir-ltr">{{ number_format($inflow, 2) }} ج.س</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-4">
                    <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-4 rounded-full transition-all duration-700"
                         style="width: {{ $inPct }}%"></div>
                </div>
            </div>

            {{-- المدفوعات --}}
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-semibold text-gray-700">المدفوعات (مشتريات + رواتب)</span>
                    <span class="font-bold text-red-600 dir-ltr">{{ number_format($outflow, 2) }} ج.س</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-4">
                    <div class="bg-gradient-to-r from-red-400 to-red-600 h-4 rounded-full transition-all duration-700"
                         style="width: {{ $outPct }}%"></div>
                </div>
            </div>

            {{-- الفائض / العجز --}}
            <div class="mt-2 p-4 rounded-xl {{ $netPositive ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200' }}">
                <div class="flex justify-between items-center">
                    <span class="font-bold {{ $netPositive ? 'text-emerald-700' : 'text-red-700' }}">
                        {{ $netPositive ? '✅ فائض نقدي' : '⚠️ عجز نقدي' }}
                    </span>
                    <span class="font-black text-lg dir-ltr {{ $netPositive ? 'text-emerald-700' : 'text-red-700' }}">
                        {{ $netPositive ? '+' : '' }}{{ number_format($data['net_cash_flow'], 2) }} ج.س
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

