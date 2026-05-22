{{-- المسار الكامل: resources/views/goods-receipts/index.blade.php --}}

@extends('layouts.app')

@section('title', 'وصولات الاستلام')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">وصولات استلام البضاعة</h1>
            <p class="text-sm text-gray-500 mt-1">إجمالي {{ $receipts->total() }} وصل</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="رقم الوصل..."
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                <option value="">كل الحالات</option>
                <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>مسودة</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>مؤكد</option>
            </select>
            <input type="date" name="from_date" value="{{ request('from_date') }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <input type="date" name="to_date" value="{{ request('to_date') }}"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <button type="submit"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                بحث
            </button>
            @if(request()->hasAny(['search', 'status', 'from_date', 'to_date']))
            <a href="{{ route('goods-receipts.index') }}"
               class="border border-gray-200 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm transition">
                مسح
            </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-teal-600 text-white text-xs">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">رقم الوصل</th>
                        <th class="px-4 py-3 text-right font-medium">أمر الشراء</th>
                        <th class="px-4 py-3 text-right font-medium">المورد</th>
                        <th class="px-4 py-3 text-right font-medium">المستودع</th>
                        <th class="px-4 py-3 text-right font-medium">تاريخ الاستلام</th>
                        <th class="px-4 py-3 text-right font-medium">الحالة</th>
                        <th class="px-4 py-3 text-right font-medium">المستخدم</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($receipts as $receipt)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-teal-700">
                            {{ $receipt->receipt_number }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('purchase-orders.show', $receipt->purchaseOrder) }}"
                               class="text-teal-600 hover:underline text-xs font-mono">
                                {{ $receipt->purchaseOrder->order_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $receipt->purchaseOrder->supplier->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $receipt->warehouse->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $receipt->received_date->format('Y/m/d') }}
                        </td>
                        <td class="px-4 py-3">
                            @if($receipt->status === 'confirmed')
                                <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">مؤكد</span>
                            @else
                                <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700">مسودة</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $receipt->user->name }}
                        </td>
                        <td class="px-4 py-3 text-left">
                            <a href="{{ route('goods-receipts.show', $receipt) }}"
                               class="text-teal-600 hover:text-teal-800 text-xs font-medium">
                                عرض
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm">لا توجد وصولات استلام</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
        <div class="p-4 border-t">
            {{ $receipts->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
