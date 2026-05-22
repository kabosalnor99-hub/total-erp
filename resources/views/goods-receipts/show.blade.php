{{-- المسار الكامل: resources/views/goods-receipts/show.blade.php --}}

@extends('layouts.app')

@section('title', 'وصل استلام ' . $goodsReceipt->receipt_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('goods-receipts.index') }}"
               class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-mono">{{ $goodsReceipt->receipt_number }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">وصل استلام بضاعة</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{-- حالة الوصل --}}
            @if($goodsReceipt->status === 'confirmed')
                <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-700 font-medium">
                    ✓ مؤكد
                </span>
            @else
                <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-700 font-medium">
                    مسودة
                </span>
                @can('goods-receipts.confirm')
                <form action="{{ route('goods-receipts.confirm', $goodsReceipt) }}" method="POST"
                      onsubmit="return confirm('هل تريد تأكيد الاستلام وتحديث المخزون؟')">
                    @csrf
                    <button type="submit"
                            class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        تأكيد الاستلام وتحديث المخزون
                    </button>
                </form>
                @endcan

                @can('goods-receipts.delete')
                <form action="{{ route('goods-receipts.destroy', $goodsReceipt) }}" method="POST"
                      onsubmit="return confirm('هل تريد حذف هذا الوصل؟')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="border border-red-200 text-red-600 hover:bg-red-50 px-4 py-2 rounded-lg text-sm transition">
                        حذف
                    </button>
                </form>
                @endcan
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
        {{ session('error') }}
    </div>
    @endif

    {{-- بيانات الوصل --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">بيانات الوصل</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">رقم الوصل</dt>
                    <dd class="font-mono font-medium text-teal-700">{{ $goodsReceipt->receipt_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">تاريخ الاستلام</dt>
                    <dd class="font-medium">{{ $goodsReceipt->received_date->format('Y/m/d') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">المستودع</dt>
                    <dd class="font-medium">{{ $goodsReceipt->warehouse->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">المستخدم</dt>
                    <dd class="font-medium">{{ $goodsReceipt->user->name }}</dd>
                </div>
                @if($goodsReceipt->notes)
                <div class="flex justify-between">
                    <dt class="text-gray-500">ملاحظات</dt>
                    <dd class="text-gray-700">{{ $goodsReceipt->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">أمر الشراء</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">رقم الأمر</dt>
                    <dd>
                        <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}"
                           class="font-mono text-teal-600 hover:underline">
                            {{ $goodsReceipt->purchaseOrder->order_number }}
                        </a>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">المورد</dt>
                    <dd class="font-medium">{{ $goodsReceipt->purchaseOrder->supplier->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">حالة الأمر</dt>
                    <dd>
                        @php
                            $colors = ['draft'=>'bg-gray-100 text-gray-600','sent'=>'bg-blue-100 text-blue-600',
                                       'partial'=>'bg-yellow-100 text-yellow-700','received'=>'bg-green-100 text-green-700',
                                       'cancelled'=>'bg-red-100 text-red-600'];
                            $labels = ['draft'=>'مسودة','sent'=>'أُرسل','partial'=>'جزئي','received'=>'مستلم','cancelled'=>'ملغي'];
                        @endphp
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $colors[$goodsReceipt->purchaseOrder->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $labels[$goodsReceipt->purchaseOrder->status] ?? $goodsReceipt->purchaseOrder->status }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- البنود --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">البنود المستلمة</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 border-b">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">المنتج</th>
                        <th class="px-4 py-3 text-right font-medium">SKU</th>
                        <th class="px-4 py-3 text-right font-medium">الكمية المستلمة</th>
                        <th class="px-4 py-3 text-right font-medium">سعر الوحدة</th>
                        <th class="px-4 py-3 text-right font-medium">الإجمالي</th>
                        <th class="px-4 py-3 text-right font-medium">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($goodsReceipt->items as $item)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $item->product->name_ar }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-400">{{ $item->product->sku }}</td>
                        <td class="px-4 py-3 text-teal-700 font-semibold">{{ number_format($item->quantity_received, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800">
                            {{ number_format($item->quantity_received * $item->unit_price, 2) }}
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $item->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-left text-sm font-semibold text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 font-bold text-teal-700">
                            {{ number_format($goodsReceipt->items->sum(fn($i) => $i->quantity_received * $i->unit_price), 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
