{{-- المسار الكامل: resources/views/purchase-orders/index.blade.php --}}

@extends('layouts.app')

@section('title', 'أوامر الشراء')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">أوامر الشراء</h1>
            <p class="text-sm text-gray-500 mt-1">إجمالي {{ $orders->total() }} أمر</p>
        </div>
        @can('purchase-orders.create')
        <a href="{{ route('purchase-orders.create') }}"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            أمر شراء جديد
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="رقم الأمر..."
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <select name="supplier_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                <option value="">كل الموردين</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->name }}
                </option>
                @endforeach
            </select>
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                <option value="">كل الحالات</option>
                <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>مسودة</option>
                <option value="sent"      {{ request('status') === 'sent'      ? 'selected' : '' }}>أُرسل</option>
                <option value="partial"   {{ request('status') === 'partial'   ? 'selected' : '' }}>مستلم جزئياً</option>
                <option value="received"  {{ request('status') === 'received'  ? 'selected' : '' }}>مستلم كاملاً</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغي</option>
            </select>
            <input type="date" name="from_date" value="{{ request('from_date') }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <input type="date" name="to_date" value="{{ request('to_date') }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">بحث</button>
            @if(request()->hasAny(['search', 'supplier_id', 'status', 'from_date', 'to_date']))
            <a href="{{ route('purchase-orders.index') }}" class="border border-gray-200 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm transition">مسح</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-teal-600 text-white text-xs">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">رقم الأمر</th>
                        <th class="px-4 py-3 text-right font-medium">المورد</th>
                        <th class="px-4 py-3 text-right font-medium">الإجمالي</th>
                        <th class="px-4 py-3 text-right font-medium">المدفوع</th>
                        <th class="px-4 py-3 text-right font-medium">المتبقي</th>
                        <th class="px-4 py-3 text-right font-medium">الحالة</th>
                        <th class="px-4 py-3 text-right font-medium">تاريخ التوقع</th>
                        <th class="px-4 py-3 text-right font-medium">المنشئ</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                    @php
                        $statusColors = [
                            'draft'     => 'bg-gray-100 text-gray-600',
                            'sent'      => 'bg-blue-100 text-blue-600',
                            'partial'   => 'bg-yellow-100 text-yellow-700',
                            'received'  => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-600',
                        ];
                        $statusLabels = [
                            'draft'     => 'مسودة',
                            'sent'      => 'أُرسل',
                            'partial'   => 'جزئي',
                            'received'  => 'مستلم',
                            'cancelled' => 'ملغي',
                        ];
                        $remaining = $order->total - $order->amount_paid;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-teal-700">{{ $order->order_number }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $order->supplier->name }}</div>
                            @if($order->supplier->company_name)
                            <div class="text-xs text-gray-400">{{ $order->supplier->company_name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ number_format($order->total, 2) }}</td>
                        <td class="px-4 py-3 text-green-600">{{ number_format($order->amount_paid, 2) }}</td>
                        <td class="px-4 py-3 {{ $remaining > 0 ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                            {{ number_format($remaining, 2) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ $order->expected_date ? $order->expected_date->format('Y/m/d') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $order->user->name }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('purchase-orders.show', $order) }}"
                                   class="text-teal-600 hover:text-teal-800 text-xs font-medium">عرض</a>
                                <a href="{{ route('purchase-orders.pdf', $order) }}"
                                   class="text-gray-500 hover:text-gray-700 text-xs" target="_blank">
                                    PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center">
                            <div class="text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <p class="text-sm">لا توجد أوامر شراء</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
