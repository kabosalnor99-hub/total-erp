{{-- المسار الكامل: resources/views/purchase-orders/create.blade.php --}}

@extends('layouts.app')

@section('title', 'أمر شراء جديد')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="purchaseOrder()">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('purchase-orders.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">أمر شراء جديد</h1>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <ul class="text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('purchase-orders.store') }}" method="POST">
        @csrf

        {{-- إذا من طلب شراء --}}
        @if($fromRequest)
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-blue-700">
                يتم إنشاء هذا الأمر من طلب الشراء رقم
                <strong>{{ $fromRequest->request_number }}</strong>
            </p>
            <input type="hidden" name="purchase_request_id" value="{{ $fromRequest->id }}">
        </div>
        @endif

        {{-- معلومات الأمر --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-700 border-b pb-2">معلومات الأمر</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">المورد <span class="text-red-500">*</span></label>
                    <select name="supplier_id" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <option value="">اختر المورد</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">تاريخ التسليم المتوقع</label>
                    <input type="date" name="expected_date" value="{{ old('expected_date') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">نسبة الضريبة % <span class="text-gray-400 text-xs">(اختياري)</span></label>
                    <input type="number" name="tax_rate" x-model="taxRate" value="{{ old('tax_rate', 0) }}"
                           min="0" max="100" step="0.1"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">خصم الأمر</label>
                    <input type="number" name="discount" x-model="orderDiscount" value="{{ old('discount', 0) }}"
                           min="0" step="0.01"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-600 mb-1">ملاحظات</label>
                    <input type="text" name="notes" value="{{ old('notes') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>
        </div>

        {{-- الأصناف --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mt-6">
            <div class="flex items-center justify-between p-5 border-b">
                <h2 class="text-base font-semibold text-gray-700">أصناف الأمر</h2>
                <button type="button" @click="addItem()"
                        class="inline-flex items-center gap-1 bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 rounded-lg text-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة صنف
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs border-b">
                        <tr>
                            <th class="px-4 py-2 text-right font-medium w-5/12">المنتج</th>
                            <th class="px-4 py-2 text-right font-medium w-1/12">الكمية</th>
                            <th class="px-4 py-2 text-right font-medium w-2/12">سعر الوحدة</th>
                            <th class="px-4 py-2 text-right font-medium w-2/12">خصم</th>
                            <th class="px-4 py-2 text-right font-medium w-2/12">الإجمالي</th>
                            <th class="px-4 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="border-b">
                                <td class="px-3 py-2">
                                    <select :name="`items[${index}][product_id]`" x-model="item.product_id"
                                            @change="onProductChange(index)"
                                            required
                                            class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 bg-white">
                                        <option value="">اختر المنتج</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                                data-price="{{ $product->purchase_price }}"
                                                data-name="{{ $product->name_ar }}">
                                            {{ $product->name_ar }} ({{ $product->sku }})
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity"
                                           @input="calcItem(index)" min="0.01" step="0.01" required
                                           class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 text-center">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" :name="`items[${index}][unit_price]`" x-model="item.unit_price"
                                           @input="calcItem(index)" min="0" step="0.01" required
                                           class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" :name="`items[${index}][discount]`" x-model="item.discount"
                                           @input="calcItem(index)" min="0" step="0.01"
                                           class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500">
                                </td>
                                <td class="px-3 py-2 text-left font-medium text-teal-700">
                                    <span x-text="parseFloat(item.total || 0).toLocaleString('ar', {minimumFractionDigits: 2})"></span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" @click="removeItem(index)"
                                            x-show="items.length > 1"
                                            class="text-red-400 hover:text-red-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- إجمالي --}}
            <div class="p-5 border-t">
                <div class="flex justify-end">
                    <div class="w-72 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>المجموع الفرعي:</span>
                            <span x-text="subtotal.toLocaleString('ar', {minimumFractionDigits: 2}) + ' ج.س'"></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>الخصم:</span>
                            <span class="text-red-500" x-text="parseFloat(orderDiscount || 0).toLocaleString('ar', {minimumFractionDigits: 2}) + ' ج.س'"></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>الضريبة (<span x-text="taxRate"></span>%):</span>
                            <span x-text="tax.toLocaleString('ar', {minimumFractionDigits: 2}) + ' ج.س'"></span>
                        </div>
                        <div class="flex justify-between font-bold text-lg text-gray-800 border-t pt-2">
                            <span>الإجمالي:</span>
                            <span class="text-teal-700" x-text="total.toLocaleString('ar', {minimumFractionDigits: 2}) + ' ج.س'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('purchase-orders.index') }}"
               class="border border-gray-200 text-gray-600 hover:bg-gray-50 px-6 py-2 rounded-lg text-sm font-medium transition">
                إلغاء
            </a>
            <button type="submit"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                إنشاء أمر الشراء
            </button>
        </div>

    </form>
</div>

<script>
function purchaseOrder() {
    const products = {!! $productsJson !!};
    const preItems = {!! $fromRequestJson !!};

    return {
        items: preItems ?? [{ product_id: '', quantity: 1, unit_price: 0, discount: 0, total: 0 }],
        taxRate: {{ old('tax_rate', 0) }},
        orderDiscount: {{ old('discount', 0) }},

        get subtotal() {
            return this.items.reduce((s, i) => s + parseFloat(i.total || 0), 0);
        },
        get tax() {
            return (parseFloat(this.taxRate) || 0) / 100 * this.subtotal;
        },
        get total() {
            return this.subtotal - (parseFloat(this.orderDiscount) || 0) + this.tax;
        },

        addItem() {
            this.items.push({ product_id: '', quantity: 1, unit_price: 0, discount: 0, total: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        },
        calcItem(index) {
            const i = this.items[index];
            i.total = (parseFloat(i.quantity) || 0) * (parseFloat(i.unit_price) || 0) - (parseFloat(i.discount) || 0);
        },
        onProductChange(index) {
            const pid = this.items[index].product_id;
            const product = products.find(p => p.id == pid);
            if (product) {
                this.items[index].unit_price = product.purchase_price;
                this.calcItem(index);
            }
        },
    };
}
</script>
@endsection
