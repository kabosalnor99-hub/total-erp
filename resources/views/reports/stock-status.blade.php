{{-- المسار: resources/views/reports/inventory/stock-status.blade.php --}}

@extends('layouts.app')

@section('title', __('reports.stock_status'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.stock_status') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('reports.stock_status_desc') }}</p>
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
                <label class="block text-xs font-medium text-gray-600 mb-1">المستودع</label>
                <select name="warehouse_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">جميع المستودعات</option>
                    @foreach(\App\Models\Warehouse::all() as $wh)
                        <option value="{{ $wh->id }}" @selected(request('warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الفئة</label>
                <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">جميع الفئات</option>
                    @foreach(\App\Models\Category::all() as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-teal-700 transition">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 inline-block ml-1"/>
                    تصفية
                </button>
                <a href="{{ route('reports.stock-status') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">إعادة تعيين</a>
            </div>
        </div>
    </form>

    {{-- Summary Card --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-blue-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي المنتجات</p>
            <p class="text-2xl font-bold text-blue-700">{{ count($data['rows']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-yellow-200 p-4">
            <p class="text-xs text-gray-500 mb-1">منتجات منخفضة المخزون</p>
            <p class="text-2xl font-bold text-yellow-600">{{ collect($data['rows'])->where('is_low', true)->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي قيمة المخزون</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($data['total_value'], 2) }} <span class="text-sm font-normal">ج.س</span></p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">SKU</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">اسم المنتج</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الفئة</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">وارد</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">صادر</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">الرصيد الحالي</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">حد إعادة الطلب</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">القيمة</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['rows'] as $row)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-teal-700">{{ $row['sku'] }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $row['category'] }}</td>
                        <td class="px-4 py-3 text-center text-blue-600">{{ number_format($row['stock_in']) }}</td>
                        <td class="px-4 py-3 text-center text-red-500">{{ number_format($row['stock_out']) }}</td>
                        <td class="px-4 py-3 text-center font-semibold {{ $row['is_low'] ? 'text-red-600' : 'text-gray-800' }}">
                            {{ number_format($row['current_stock']) }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-400">{{ number_format($row['reorder_point']) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-sm text-gray-700">{{ number_format($row['stock_value'], 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($row['is_low'])
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    <x-heroicon-s-exclamation-triangle class="w-3 h-3"/> منخفض
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <x-heroicon-s-check-circle class="w-3 h-3"/> طبيعي
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-gray-400">لا توجد بيانات مخزون</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="7" class="px-4 py-3 font-semibold text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 text-left font-bold text-gray-800 font-mono">{{ number_format($data['total_value'], 2) }} ج.س</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Export --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('reports.export-pdf', 'stock-status') }}?{{ http_build_query(request()->all()) }}"
           class="flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4"/> تصدير PDF
        </a>
    </div>

</div>
@endsection
