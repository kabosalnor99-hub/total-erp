{{-- المسار: resources/views/reports/inventory/movements.blade.php --}}

@extends('layouts.app')

@section('title', __('reports.stock_movements'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.stock_movements') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('reports.stock_movements_desc') }}</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
            العودة للتقارير
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">المنتج</label>
                <select name="product_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">جميع المنتجات</option>
                    @foreach($data['products'] as $product)
                        <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from', $data['date_from']) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to', $data['date_to']) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-amber-700 transition">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 inline-block ml-1"/>
                    تصفية
                </button>
                <a href="{{ route('reports.stock-movements') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">إعادة تعيين</a>
            </div>
        </div>
    </form>

    {{-- Summary Cards --}}
    @php
        $totalIn  = $data['rows']->where('type', 'in')->sum('quantity');
        $totalOut = $data['rows']->where('type', 'out')->sum('quantity');
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-blue-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الوارد</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($totalIn) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الصادر</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($totalOut) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">عدد الحركات</p>
            <p class="text-2xl font-bold text-gray-800">{{ $data['rows']->count() }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">التاريخ</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">المنتج</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">المستودع</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">نوع الحركة</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">الكمية</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">المرجع</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['rows'] as $row)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($row->movement_date)->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $row->product->name_ar ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $row->warehouse->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($row->type === 'in')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    <x-heroicon-s-arrow-down-tray class="w-3 h-3"/> وارد
                                </span>
                            @elseif($row->type === 'out')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    <x-heroicon-s-arrow-up-tray class="w-3 h-3"/> صادر
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    تحويل
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-semibold {{ $row->type === 'in' ? 'text-blue-600' : 'text-red-600' }}">
                            {{ $row->type === 'in' ? '+' : '-' }}{{ number_format($row->quantity) }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ $row->reference ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $row->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">لا توجد حركات مخزون في هذه الفترة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Export --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('reports.export-pdf', 'stock-movements') }}?{{ http_build_query(request()->all()) }}"
           class="flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4"/> تصدير PDF
        </a>
    </div>

</div>
@endsection
