{{-- المسار الكامل: resources/views/goods-receipts/create.blade.php --}}

@extends('layouts.app')

@section('title', 'استلام بضاعة')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="goodsReceipt()">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('purchase-orders.show', $order) }}"
           class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">استلام بضاعة</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                أمر الشراء: <span class="font-mono text-teal-600">{{ $order->order_number }}</span>
                — {{ $order->supplier->name }}
            </p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <ul class="text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error)
            <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('goods-receipts.store') }}" method="POST">
        @csrf
        <input type="hidden" name="purchase_order_id" value="{{ $order->id }}">

        <div class="space-y-5">

            {{-- بيانات الاستلام --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4">بيانات الاستلام</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            المستودع <span class="text-red-500">*</span>
                        </label>
                        <select name="warehouse_id"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                required>
                            <option value="">اختر المستودع</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            تاريخ الاستلام <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="received_date"
                               value="{{ old('received_date', now()->toDateString()) }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                               required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                        <textarea name="notes" rows="2"
                                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- بنود الاستلام --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4">
                    البنود المراد استلامها
                    <span class="text-xs text-gray-400 font-normal mr-1">(أدخل الكمية المستلمة فعلياً)</span>
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 border-b">
                            <tr>
                                <th class="px-3 py-2 text-right font-medium">المنتج</th>
                                <th class="px-3 py-2 text-right font-medium">الكمية المطلوبة</th>
                                <th class="px-3 py-2 text-right font-medium">المستلم مسبقاً</th>
                                <th class="px-3 py-2 text-right font-medium">المتبقي</th>
                                <th class="px-3 py-2 text-right font-medium w-32">الكمية المستلمة الآن</th>
                                <th class="px-3 py-2 text-right font-medium w-32">سعر الوحدة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($pendingItems as $index => $item)
                            <tr>
                                <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $item->id }}">
                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">

                                <td class="px-3 py-3">
                                    <div class="font-medium text-gray-800">{{ $item->product->name_ar }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $item->product->sku }}</div>
                                </td>
                                <td class="px-3 py-3 text-gray-600">
                                    {{ number_format($item->quantity, 2) }}
                                </td>
                                <td class="px-3 py-3 text-green-600">
                                    {{ number_format($item->received_quantity, 2) }}
                                </td>
                                <td class="px-3 py-3 text-orange-500 font-medium">
                                    {{ number_format($item->quantity - $item->received_quantity, 2) }}
                                </td>
                                <td class="px-3 py-3">
                                    <input type="number" name="items[{{ $index }}][quantity_received]"
                                           value="{{ old("items.{$index}.quantity_received", $item->quantity - $item->received_quantity) }}"
                                           min="0.01"
                                           max="{{ $item->quantity - $item->received_quantity }}"
                                           step="0.01"
                                           class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                           required>
                                </td>
                                <td class="px-3 py-3">
                                    <input type="number" name="items[{{ $index }}][unit_price]"
                                           value="{{ old("items.{$index}.unit_price", $item->unit_price) }}"
                                           min="0" step="0.01"
                                           class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                           required>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($pendingItems->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <p class="text-sm">جميع بنود هذا الأمر استُلمت بالكامل</p>
                </div>
                @endif
            </div>

            {{-- أزرار الحفظ --}}
            @if($pendingItems->isNotEmpty())
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('purchase-orders.show', $order) }}"
                   class="border border-gray-200 text-gray-600 hover:bg-gray-50 px-5 py-2 rounded-lg text-sm transition">
                    إلغاء
                </a>
                <button type="submit"
                        class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                    حفظ وصل الاستلام
                </button>
            </div>
            @endif

        </div>
    </form>
</div>

@push('scripts')
<script>
function goodsReceipt() {
    return {};
}
</script>
@endpush
@endsection
