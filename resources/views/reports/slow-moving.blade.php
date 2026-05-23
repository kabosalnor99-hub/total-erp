{{-- المسار: resources/views/reports/inventory/slow-moving.blade.php --}}

@extends('layouts.app')

@section('title', __('reports.slow_moving'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.slow_moving') }}</h1>
            <p class="text-sm text-gray-500 mt-1">منتجات لم تتحرك خلال آخر {{ $data['days'] }} يوم</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
            العودة للتقارير
        </a>
    </div>

    {{-- Info Banner --}}
    <div class="bg-stone-50 border border-stone-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <x-heroicon-o-clock class="w-5 h-5 text-stone-500 flex-shrink-0"/>
        <p class="text-sm text-stone-700">
            يعرض هذا التقرير المنتجات التي لم تسجّل أي حركة مخزون (وارد أو صادر) خلال <strong>{{ $data['days'] }} يوم</strong> الماضية.
        </p>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-stone-200 p-4">
            <p class="text-xs text-gray-500 mb-1">عدد المنتجات بطيئة الحركة</p>
            <p class="text-3xl font-bold text-stone-700">{{ count($data['rows']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-stone-200 p-4">
            <p class="text-xs text-gray-500 mb-1">فترة المراقبة</p>
            <p class="text-3xl font-bold text-stone-700">{{ $data['days'] }} <span class="text-base font-normal text-gray-500">يوم</span></p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">قائمة المنتجات بطيئة الحركة</h2>
            <span class="text-xs bg-stone-100 text-stone-700 px-2 py-0.5 rounded-full font-medium">{{ count($data['rows']) }} منتج</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">SKU</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">اسم المنتج</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الفئة</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">آخر حركة</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">عدد الأيام بلا حركة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['rows'] as $row)
                    @php
                        $daysSince = $row['last_movement'] && $row['last_movement'] !== __('reports.never')
                            ? \Carbon\Carbon::parse($row['last_movement'])->diffInDays(now())
                            : null;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-teal-700">{{ $row['sku'] }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $row['category'] }}</td>
                        <td class="px-4 py-3 text-center text-gray-500 text-xs">
                            @if($row['last_movement'] && $row['last_movement'] !== __('reports.never'))
                                {{ \Carbon\Carbon::parse($row['last_movement'])->format('Y/m/d') }}
                            @else
                                <span class="text-gray-400 italic">لم تُسجَّل حركة</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($daysSince !== null)
                                <span class="inline-block px-3 py-0.5 rounded-full text-xs font-semibold
                                    {{ $daysSince > 180 ? 'bg-red-100 text-red-700' : ($daysSince > 90 ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700') }}">
                                    {{ $daysSince }} يوم
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">لا توجد منتجات بطيئة الحركة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Export --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('reports.export-pdf', 'slow-moving') }}"
           class="flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4"/> تصدير PDF
        </a>
    </div>

</div>
@endsection
