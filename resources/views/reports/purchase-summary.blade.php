{{-- المسار: resources/views/reports/purchases/summary.blade.php --}}

@extends('layouts.app')

@section('title', __('reports.purchase_summary'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.purchase_summary') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('reports.purchase_summary_desc') }}</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
            العودة للتقارير
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from', $data['date_from'] ?? now()->startOfMonth()->toDateString()) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to', $data['date_to'] ?? now()->toDateString()) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 inline-block ml-1"/>
                    تصفية
                </button>
                <a href="{{ route('reports.purchase-summary') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">إعادة تعيين</a>
            </div>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-teal-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي قيمة المشتريات</p>
            <p class="text-2xl font-bold text-teal-700">{{ number_format($data['total'], 2) }} <span class="text-sm font-normal text-gray-400">ج.س</span></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">عدد الموردين</p>
            <p class="text-2xl font-bold text-gray-800">{{ $data['rows']->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي أوامر الشراء</p>
            <p class="text-2xl font-bold text-gray-800">{{ $data['rows']->sum('total_orders') }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">ملخص المشتريات حسب المورد</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">#</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">المورد</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">عدد الأوامر</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">إجمالي المبلغ</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">النسبة من الإجمالي</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['rows'] as $i => $row)
                    @php
                        $pct = $data['total'] > 0 ? round(($row->total_amount / $data['total']) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $row->supplier->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $row->total_orders }}</td>
                        <td class="px-4 py-3 text-left font-mono text-sm font-semibold text-teal-700">{{ number_format($row->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full bg-teal-500" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('reports.supplier-statement') }}?supplier_id={{ $row->supplier_id }}&date_from={{ $data['date_from'] ?? now()->startOfMonth()->toDateString() }}&date_to={{ $data['date_to'] ?? now()->toDateString() }}"
                               class="text-xs text-teal-600 hover:underline">كشف الحساب</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">لا توجد مشتريات في هذه الفترة</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr class="font-semibold">
                        <td colspan="2" class="px-4 py-3 text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 text-center">{{ $data['rows']->sum('total_orders') }}</td>
                        <td class="px-4 py-3 text-left font-mono text-teal-700">{{ number_format($data['total'], 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Export --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('reports.export-pdf', 'purchase-summary') }}?{{ http_build_query(request()->all()) }}"
           class="flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4"/> تصدير PDF
        </a>
    </div>

</div>
@endsection
