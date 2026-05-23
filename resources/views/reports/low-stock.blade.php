{{-- المسار: resources/views/reports/inventory/low-stock.blade.php --}}

@extends('layouts.app')

@section('title', __('reports.low_stock'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.low_stock') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('reports.low_stock_desc') }}</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
            العودة للتقارير
        </a>
    </div>

    {{-- Alert Banner --}}
    @if(count($data['rows']) > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-orange-500 flex-shrink-0"/>
        <div>
            <p class="font-semibold text-orange-800">تحذير: {{ count($data['rows']) }} منتج تحت حد إعادة الطلب</p>
            <p class="text-sm text-orange-600 mt-0.5">يرجى مراجعة هذه المنتجات وإصدار أوامر شراء للكميات الناقصة.</p>
        </div>
    </div>
    @else
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <x-heroicon-o-check-circle class="w-6 h-6 text-green-500 flex-shrink-0"/>
        <p class="font-semibold text-green-800">جميع المنتجات فوق حد إعادة الطلب — لا توجد تنبيهات</p>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">المنتجات المنخفضة</h2>
            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">{{ count($data['rows']) }} منتج</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">SKU</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">اسم المنتج</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">المخزون الحالي</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">حد إعادة الطلب</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">الكمية الناقصة</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['rows'] as $row)
                    <tr class="hover:bg-red-50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-teal-700">{{ $row['sku'] }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-red-600">{{ number_format($row['current_stock']) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ number_format($row['reorder_point']) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block bg-orange-100 text-orange-700 font-semibold px-3 py-0.5 rounded-full text-xs">
                                + {{ number_format($row['shortage']) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('purchase-requests.create') }}?product_id={{ $row['id'] }}"
                               class="text-xs text-blue-600 hover:underline">إنشاء طلب شراء</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">لا توجد منتجات منخفضة المخزون</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Export --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('reports.export-pdf', 'low-stock') }}"
           class="flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4"/> تصدير PDF
        </a>
    </div>

</div>
@endsection
