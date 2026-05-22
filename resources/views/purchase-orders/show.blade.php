{{-- المسار الكامل: resources/views/purchase-orders/show.blade.php --}}

@extends('layouts.app')

@section('title', 'أمر شراء — ' . $purchaseOrder->order_number)

@section('content')
<div class="space-y-6" x-data="{ showReceiveForm: false, showPayForm: false }">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-orders.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $purchaseOrder->order_number }}</h1>
                <p class="text-sm text-gray-500">{{ $purchaseOrder->supplier->name }}</p>
            </div>
            @php
                $statusColors = ['draft'=>'bg-gray-100 text-gray-600','sent'=>'bg-blue-100 text-blue-600','partial'=>'bg-yellow-100 text-yellow-700','received'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-600'];
                $statusLabels = ['draft'=>'مسودة','sent'=>'أُرسل','partial'=>'جزئي','received'=>'مستلم كاملاً','cancelled'=>'ملغي'];
            @endphp
            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColors[$purchaseOrder->status] ?? '' }}">
                {{ $statusLabels[$purchaseOrder->status] ?? $purchaseOrder->status }}
            </span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('purchase-orders.pdf', $purchaseOrder) }}" target="_blank"
               class="inline-flex items-center gap-2 border border-gray-200 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                طباعة PDF
            </a>

            @if($purchaseOrder->status === 'draft')
            @can('purchase-orders.send')
            <form action="{{ route('purchase-orders.mark-sent', $purchaseOrder) }}" method="POST" class="inline">
                @csrf @method('PATCH')
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    تأكيد الإرسال للمورد
                </button>
            </form>
            @endcan
            @endif

            @if(in_array($purchaseOrder->status, ['sent', 'partial']))
            <button @click="showReceiveForm = !showReceiveForm"
                    class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                استلام بضاعة
            </button>
            @endif

            @if(in_array($purchaseOrder->status, ['draft', 'sent']))
            @can('purchase-orders.cancel')
            <form action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" method="POST" class="inline"
                  onsubmit="return confirm('هل تريد إلغاء هذا الأمر؟')">
                @csrf @method('PATCH')
                <button type="submit" class="border border-red-200 text-red-500 hover:bg-red-50 px-4 py-2 rounded-lg text-sm transition">
                    إلغاء الأمر
                </button>
            </form>
            @endcan
            @endif
        </div>
    </div>

    {{-- نموذج استلام البضاعة --}}
    @if(in_array($purchaseOrder->status, ['sent', 'partial']))
    <div x-show="showReceiveForm" x-cloak x-transition
         class="bg-white rounded-xl shadow-sm border border-teal-200 p-6">
        <h2 class="text-base font-semibold text-teal-700 mb-4">استلام البضاعة</h2>
        <a href="{{ route('goods-receipts.create', ['purchase_order_id' => $purchaseOrder->id]) }}"
           class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
            فتح نموذج الاستلام الكامل
        </a>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- تفاصيل الأمر --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
            <h2 class="text-base font-semibold text-gray-700 border-b pb-2">تفاصيل الأمر</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">المورد:</span>
                    <a href="{{ route('suppliers.show', $purchaseOrder->supplier) }}" class="text-teal-600 hover:underline font-medium">
                        {{ $purchaseOrder->supplier->name }}
                    </a>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">تاريخ الإنشاء:</span>
                    <span class="text-gray-700">{{ $purchaseOrder->created_at->format('Y/m/d') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">تاريخ التسليم المتوقع:</span>
                    <span class="text-gray-700">{{ $purchaseOrder->expected_date?->format('Y/m/d') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">المنشئ:</span>
                    <span class="text-gray-700">{{ $purchaseOrder->user->name }}</span>
                </div>
                @if($purchaseOrder->purchaseRequest)
                <div class="flex justify-between">
                    <span class="text-gray-500">طلب الشراء:</span>
                    <a href="{{ route('purchase-requests.show', $purchaseOrder->purchaseRequest) }}" class="text-teal-600 hover:underline text-xs">
                        {{ $purchaseOrder->purchaseRequest->request_number }}
                    </a>
                </div>
                @endif
                @if($purchaseOrder->notes)
                <div class="pt-2 border-t">
                    <p class="text-gray-500 text-xs">ملاحظات:</p>
                    <p class="text-gray-700 mt-1">{{ $purchaseOrder->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- الإجماليات المالية --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
            <h2 class="text-base font-semibold text-gray-700 border-b pb-2">الملخص المالي</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">المجموع الفرعي:</span>
                    <span class="font-medium">{{ number_format($purchaseOrder->subtotal, 2) }} ج.س</span>
                </div>
                @if($purchaseOrder->discount > 0)
                <div class="flex justify-between">
                    <span class="text-gray-500">الخصم:</span>
                    <span class="text-red-500">- {{ number_format($purchaseOrder->discount, 2) }} ج.س</span>
                </div>
                @endif
                @if($purchaseOrder->tax > 0)
                <div class="flex justify-between">
                    <span class="text-gray-500">الضريبة:</span>
                    <span>{{ number_format($purchaseOrder->tax, 2) }} ج.س</span>
                </div>
                @endif
                <div class="flex justify-between font-bold text-base text-gray-800 border-t pt-2">
                    <span>الإجمالي:</span>
                    <span class="text-teal-700">{{ number_format($purchaseOrder->total, 2) }} ج.س</span>
                </div>
                <div class="flex justify-between text-green-600">
                    <span>المدفوع:</span>
                    <span>{{ number_format($purchaseOrder->amount_paid, 2) }} ج.س</span>
                </div>
                <div class="flex justify-between font-semibold {{ ($purchaseOrder->total - $purchaseOrder->amount_paid) > 0 ? 'text-red-500' : 'text-gray-400' }}">
                    <span>المتبقي:</span>
                    <span>{{ number_format($purchaseOrder->total - $purchaseOrder->amount_paid, 2) }} ج.س</span>
                </div>
            </div>
        </div>

        {{-- تسجيل دفعة --}}
        @if(in_array($purchaseOrder->status, ['sent', 'partial', 'received']))
        @can('purchase-orders.pay')
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-700 border-b pb-2 mb-4">تسجيل دفعة</h2>
            <form action="{{ route('suppliers.pay', $purchaseOrder->supplier) }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">المبلغ</label>
                    <input type="number" name="amount" min="0.01" step="0.01"
                           value="{{ $purchaseOrder->total - $purchaseOrder->amount_paid }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">طريقة الدفع</label>
                    <select name="method" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="cash">نقدي</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="check">شيك</option>
                        <option value="other">أخرى</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">تاريخ الدفع</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">رقم المرجع</label>
                    <input type="text" name="reference"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-2 rounded-lg text-sm font-medium transition">
                    تسجيل الدفعة
                </button>
            </form>
        </div>
        @endcan
        @endif

    </div>

    {{-- بنود الأمر --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-5 border-b">
            <h2 class="text-base font-semibold text-gray-700">بنود الأمر</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs border-b">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">المنتج</th>
                        <th class="px-4 py-3 text-right font-medium">الكمية المطلوبة</th>
                        <th class="px-4 py-3 text-right font-medium">المستلمة</th>
                        <th class="px-4 py-3 text-right font-medium">سعر الوحدة</th>
                        <th class="px-4 py-3 text-right font-medium">الخصم</th>
                        <th class="px-4 py-3 text-right font-medium">الإجمالي</th>
                        <th class="px-4 py-3 text-right font-medium">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($purchaseOrder->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $item->product->name_ar }}</div>
                            <div class="text-xs text-gray-400">{{ $item->product->sku }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">{{ number_format($item->quantity, 2) }}</td>
                        <td class="px-4 py-3 text-center {{ $item->received_quantity >= $item->quantity ? 'text-green-600 font-medium' : 'text-yellow-600' }}">
                            {{ number_format($item->received_quantity, 2) }}
                        </td>
                        <td class="px-4 py-3">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-3 text-red-400">{{ $item->discount > 0 ? number_format($item->discount, 2) : '—' }}</td>
                        <td class="px-4 py-3 font-medium text-teal-700">{{ number_format($item->total, 2) }}</td>
                        <td class="px-4 py-3">
                            @if($item->received_quantity <= 0)
                            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-500 rounded-full">لم يُستلم</span>
                            @elseif($item->received_quantity >= $item->quantity)
                            <span class="px-2 py-0.5 text-xs bg-green-100 text-green-600 rounded-full">مكتمل</span>
                            @else
                            <span class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-600 rounded-full">جزئي</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- وصولات الاستلام --}}
    @if($purchaseOrder->goodsReceipts->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-5 border-b">
            <h2 class="text-base font-semibold text-gray-700">وصولات الاستلام</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs border-b">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">رقم الوصل</th>
                        <th class="px-4 py-3 text-right font-medium">المستودع</th>
                        <th class="px-4 py-3 text-right font-medium">تاريخ الاستلام</th>
                        <th class="px-4 py-3 text-right font-medium">الحالة</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($purchaseOrder->goodsReceipts as $receipt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-teal-700">{{ $receipt->receipt_number }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $receipt->warehouse->name }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $receipt->received_date->format('Y/m/d') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $receipt->status === 'confirmed' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                                {{ $receipt->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('goods-receipts.show', $receipt) }}" class="text-teal-600 hover:underline text-xs">عرض</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- المدفوعات --}}
    @if($purchaseOrder->payments->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-5 border-b">
            <h2 class="text-base font-semibold text-gray-700">المدفوعات</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs border-b">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">التاريخ</th>
                        <th class="px-4 py-3 text-right font-medium">المبلغ</th>
                        <th class="px-4 py-3 text-right font-medium">طريقة الدفع</th>
                        <th class="px-4 py-3 text-right font-medium">المرجع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($purchaseOrder->payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $payment->payment_date->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 font-semibold text-green-600">{{ number_format($payment->amount, 2) }} ج.س</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ ['cash'=>'نقدي','bank_transfer'=>'تحويل بنكي','check'=>'شيك','other'=>'أخرى'][$payment->method] ?? $payment->method }}
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $payment->reference ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
