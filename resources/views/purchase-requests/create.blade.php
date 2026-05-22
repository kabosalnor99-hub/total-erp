{{-- المسار الكامل: resources/views/purchase-requests/create.blade.php --}}

@extends('layouts.app')

@section('title', 'طلب شراء جديد')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="purchaseRequest()">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('purchase-requests.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">طلب شراء جديد</h1>
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

    <form action="{{ route('purchase-requests.store') }}" method="POST">
        @csrf

        {{-- معلومات الطلب --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-700 border-b pb-2">معلومات الطلب</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">مطلوب بتاريخ <span class="text-gray-400 text-xs">(اختياري)</span></label>
                    <input type="date" name="needed_by" value="{{ old('needed_by') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">ملاحظات</label>
                    <input type="text" name="notes" value="{{ old('notes') }}"
                           placeholder="أي ملاحظات إضافية..."
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>
        </div>

        {{-- الأصناف المطلوبة --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mt-6">
            <div class="flex items-center justify-between p-5 border-b">
                <h2 class="text-base font-semibold text-gray-700">الأصناف المطلوبة</h2>
                <button type="button" @click="addItem()"
                        class="inline-flex items-center gap-1 bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 rounded-lg text-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة صنف
                </button>
            </div>

            <div class="p-5 space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="grid grid-cols-12 gap-3 items-start p-3 bg-gray-50 rounded-lg">

                        {{-- المنتج --}}
                        <div class="col-span-4">
                            <label class="block text-xs text-gray-500 mb-1">المنتج <span class="text-red-500">*</span></label>
                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" required
                                    class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white">
                                <option value="">اختر المنتج</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}">
                                    {{ $product->name_ar }} ({{ $product->sku }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- الكمية --}}
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">الكمية <span class="text-red-500">*</span></label>
                            <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity"
                                   min="0.01" step="0.01" required
                                   class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        </div>

                        {{-- السعر التقديري --}}
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">السعر التقديري</label>
                            <input type="number" :name="`items[${index}][estimated_price]`" x-model="item.estimated_price"
                                   min="0" step="0.01"
                                   class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        </div>

                        {{-- ملاحظات --}}
                        <div class="col-span-3">
                            <label class="block text-xs text-gray-500 mb-1">ملاحظات</label>
                            <input type="text" :name="`items[${index}][notes]`" x-model="item.notes"
                                   class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        </div>

                        {{-- حذف --}}
                        <div class="col-span-1 flex items-end pb-1">
                            <button type="button" @click="removeItem(index)"
                                    x-show="items.length > 1"
                                    class="w-full text-red-400 hover:text-red-600 transition p-2 flex justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>

                    </div>
                </template>

                <p x-show="items.length === 0" class="text-center text-gray-400 text-sm py-4">
                    أضف أصناف للطلب باستخدام الزر أعلاه
                </p>
            </div>

            {{-- ملخص --}}
            <div class="p-5 border-t bg-gray-50 rounded-b-xl" x-show="items.length > 0">
                <div class="flex justify-end">
                    <div class="text-sm text-gray-600 space-y-1">
                        <div class="flex justify-between gap-8">
                            <span>عدد الأصناف:</span>
                            <span class="font-semibold" x-text="items.length"></span>
                        </div>
                        <div class="flex justify-between gap-8">
                            <span>التكلفة التقديرية:</span>
                            <span class="font-semibold text-teal-700" x-text="totalEstimated.toLocaleString('ar') + ' ج.س'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('purchase-requests.index') }}"
               class="border border-gray-200 text-gray-600 hover:bg-gray-50 px-6 py-2 rounded-lg text-sm font-medium transition">
                إلغاء
            </a>
            <button type="submit" :disabled="items.length === 0"
                    class="bg-teal-600 hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                إرسال الطلب
            </button>
        </div>

    </form>
</div>

<script>
function purchaseRequest() {
    return {
        items: [{ product_id: '', quantity: 1, estimated_price: '', notes: '' }],

        get totalEstimated() {
            return this.items.reduce((sum, i) => {
                return sum + ((parseFloat(i.quantity) || 0) * (parseFloat(i.estimated_price) || 0));
            }, 0);
        },

        addItem() {
            this.items.push({ product_id: '', quantity: 1, estimated_price: '', notes: '' });
        },

        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        },
    };
}
</script>
@endsection
