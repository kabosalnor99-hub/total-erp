{{-- المسار الكامل: resources/views/warehouses/movements.blade.php --}}

@extends('layouts.app')

@section('title', 'حركات مستودع: ' . $warehouse->name)

@section('content')
<div class="space-y-6">

    {{-- ─── رأس الصفحة ──────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('warehouses.index') }}"
               class="text-gray-400 hover:text-[#00838F] transition-colors">
                <i class="fas fa-arrow-right text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">حركات المخزون</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    <i class="fas fa-warehouse text-[#00838F] ml-1"></i>
                    {{ $warehouse->name }}
                    @if($warehouse->code)
                    <span class="font-mono text-xs bg-gray-100 px-1 rounded">{{ $warehouse->code }}</span>
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('stock-movements.create') }}"
           class="btn-primary flex items-center gap-2 text-sm">
            <i class="fas fa-plus"></i>
            إضافة حركة يدوية
        </a>
    </div>

    {{-- ─── فلاتر ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('warehouses.movements', $warehouse) }}"
              class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">نوع الحركة</label>
                <select name="type"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none bg-white">
                    <option value="">الكل</option>
                    <option value="in"         {{ request('type') === 'in'         ? 'selected' : '' }}>إضافة مخزون</option>
                    <option value="out"        {{ request('type') === 'out'        ? 'selected' : '' }}>إخراج مخزون</option>
                    <option value="transfer"   {{ request('type') === 'transfer'   ? 'selected' : '' }}>تحويل</option>
                    <option value="adjust"     {{ request('type') === 'adjust'     ? 'selected' : '' }}>تسوية</option>
                    <option value="return_in"  {{ request('type') === 'return_in'  ? 'selected' : '' }}>مرتجع شراء</option>
                    <option value="return_out" {{ request('type') === 'return_out' ? 'selected' : '' }}>مرتجع بيع</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">من تاريخ</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-[#00838F] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#005F6B] transition-colors">
                    <i class="fas fa-filter ml-1"></i>
                    تصفية
                </button>
                <a href="{{ route('warehouses.movements', $warehouse) }}"
                   class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                    إعادة تعيين
                </a>
            </div>
        </form>
    </div>

    {{-- ─── جدول الحركات ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#00838F] text-white">
                        <th class="px-4 py-3 text-right font-semibold">#</th>
                        <th class="px-4 py-3 text-right font-semibold">التاريخ والوقت</th>
                        <th class="px-4 py-3 text-right font-semibold">المنتج</th>
                        <th class="px-4 py-3 text-center font-semibold">النوع</th>
                        <th class="px-4 py-3 text-center font-semibold">الكمية</th>
                        <th class="px-4 py-3 text-center font-semibold">قبل</th>
                        <th class="px-4 py-3 text-center font-semibold">بعد</th>
                        <th class="px-4 py-3 text-right font-semibold">السبب / الملاحظة</th>
                        <th class="px-4 py-3 text-right font-semibold">المستخدم</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $movement)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $movement->id }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $movement->created_at->format('Y-m-d') }}
                            <br>
                            <span class="text-xs text-gray-400">{{ $movement->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('products.show', $movement->product) }}"
                               class="font-medium text-gray-800 hover:text-[#00838F]">
                                {{ $movement->product->name_ar }}
                            </a>
                            <p class="text-xs text-gray-400 font-mono">{{ $movement->product->sku }}</p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $colors = [
                                    'in'         => 'bg-green-100 text-green-700',
                                    'return_in'  => 'bg-green-100 text-green-700',
                                    'out'        => 'bg-red-100 text-red-700',
                                    'return_out' => 'bg-red-100 text-red-700',
                                    'transfer'   => 'bg-blue-100 text-blue-700',
                                    'adjust'     => 'bg-yellow-100 text-yellow-700',
                                ];
                                $colorClass = $colors[$movement->type] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                {{ $movement->type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-bold
                            {{ in_array($movement->type, ['in','return_in']) ? 'text-green-600' : 'text-red-600' }}">
                            {{ in_array($movement->type, ['in','return_in']) ? '+' : '-' }}{{ number_format($movement->quantity) }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">
                            {{ number_format($movement->quantity_before) }}
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-700">
                            {{ number_format($movement->quantity_after) }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs">
                            {{ $movement->reason ?? $movement->notes ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">
                            {{ $movement->user?->name ?? 'النظام' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                            <i class="fas fa-exchange-alt text-4xl mb-3 block"></i>
                            لا توجد حركات مخزون لهذا المستودع
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($movements->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $movements->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
