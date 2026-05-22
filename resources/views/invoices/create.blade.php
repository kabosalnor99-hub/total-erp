{{-- المسار: resources/views/invoices/create.blade.php --}}
@extends('layouts.app')

@section('title', 'فاتورة جديدة')

@section('content')
<div class="max-w-5xl mx-auto space-y-6"
     x-data="invoiceForm()"
     x-init="init()">

    {{-- رأس الصفحة --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('invoices.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
            <i class="fa fa-arrow-right"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">إنشاء فاتورة جديدة</h1>
            <p class="text-sm text-gray-500">أدخل بيانات الفاتورة والبنود</p>
        </div>
    </div>

    <form action="{{ route('invoices.store') }}" method="POST" x-ref="invoiceForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ─── الجانب الأيمن — البنود ──────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- البنود --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b flex items-center justify-between">
                        <h2 class="font-semibold text-gray-700">بنود الفاتورة</h2>
                        <span class="text-xs text-gray-400" x-text="items.length + ' بند'"></span>
                    </div>

                    {{-- رأس الجدول --}}
                    <div class="hidden md:grid grid-cols-12 gap-2 px-4 py-2 bg-gray-50 border-b text-xs font-semibold text-gray-500">
                        <div class="col-span-4">المنتج</div>
                        <div class="col-span-2 text-center">الكمية</div>
                        <div class="col-span-2 text-center">السعر</div>
                        <div class="col-span-2 text-center">خصم %</div>
                        <div class="col-span-1 text-center">الإجمالي</div>
                        <div class="col-span-1"></div>
                    </div>

                    {{-- البنود --}}
                    <div class="divide-y divide-gray-50 px-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="py-3 grid grid-cols-12 gap-2 items-center">
                                {{-- المنتج --}}
                                <div class="col-span-4">
                                    <select :name="'items['+index+'][product_id]'"
                                            x-model="item.product_id"
                                            @change="onProductChange(index)"
                                            required
                                            class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                                        <option value="">-- اختر منتج --</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                                data-price="{{ $product->sale_price }}"
                                                data-stock="{{ $product->quantity }}"
                                                data-unit="{{ $product->unit }}">
                                            {{ $product->name_ar }} ({{ $product->sku }}) — متوفر: {{ $product->quantity }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="item.unit ? 'الوحدة: ' + item.unit : ''"></p>
                                </div>
                                {{-- الكمية --}}
                                <div class="col-span-2">
                                    <input type="number" :name="'items['+index+'][quantity]'"
                                           x-model.number="item.quantity"
                                           @input="calcLine(index)"
                                           min="1" :max="item.stock"
                                           required
                                           class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                                </div>
                                {{-- السعر --}}
                                <div class="col-span-2">
                                    <input type="number" :name="'items['+index+'][unit_price]'"
                                           x-model.number="item.unit_price"
                                           @input="calcLine(index)"
                                           min="0" step="0.01"
                                           required
                                           class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                                </div>
                                {{-- خصم % --}}
                                <div class="col-span-2">
                                    <input type="number" :name="'items['+index+'][discount_percent]'"
                                           x-model.number="item.discount_percent"
                                           @input="calcLine(index)"
                                           min="0" max="100" step="0.01"
                                           class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                                </div>
                                {{-- الإجمالي --}}
                                <div class="col-span-1 text-center font-semibold text-gray-800 text-sm"
                                     x-text="formatNum(item.total)"></div>
                                {{-- حذف --}}
                                <div class="col-span-1 text-center">
                                    <button type="button" @click="removeItem(index)"
                                            class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition">
                                        <i class="fa fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- إضافة بند --}}
                    <div class="px-4 py-3 border-t">
                        <button type="button" @click="addItem()"
                                class="flex items-center gap-2 text-primary hover:bg-primary/5 px-3 py-2 rounded-lg transition text-sm font-medium">
                            <i class="fa fa-plus"></i> إضافة منتج
                        </button>
                    </div>
                </div>

                {{-- ملاحظات --}}
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <textarea name="notes" rows="2"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none resize-none"
                              placeholder="ملاحظات اختيارية على الفاتورة...">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- ─── الجانب الأيسر — الملخص والعميل ────────────────────── --}}
            <div class="space-y-5">

                {{-- العميل والنوع --}}
                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h2 class="font-semibold text-gray-700 border-b pb-2">بيانات الفاتورة</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">العميل</label>
                        <select name="customer_id"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            <option value="">عميل نقدي</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                                @if($customer->balance > 0) (رصيد: {{ number_format($customer->balance,2) }}) @endif
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نوع الفاتورة <span class="text-red-500">*</span></label>
                        <select name="type" required x-model="invoiceType"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            <option value="cash">نقدي</option>
                            <option value="credit">آجل</option>
                            <option value="partial">جزئي</option>
                        </select>
                    </div>

                    <div x-show="invoiceType === 'credit' || invoiceType === 'partial'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}"
                               :required="invoiceType !== 'cash'"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المستودع <span class="text-red-500">*</span></label>
                        <select name="warehouse_id" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                            @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- الإجماليات --}}
                <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
                    <h2 class="font-semibold text-gray-700 border-b pb-2">ملخص الفاتورة</h2>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">الإجمالي الفرعي</span>
                            <span class="font-medium" x-text="formatNum(subtotal)"></span>
                        </div>

                        {{-- خصم --}}
                        <div>
                            <label class="text-gray-500">خصم %</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="number" name="discount_percent"
                                       x-model.number="discountPercent"
                                       @input="calcTotals()"
                                       min="0" max="100" step="0.01"
                                       class="w-20 border border-gray-200 rounded px-2 py-1 text-sm text-center focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                                <span class="text-gray-400 text-xs">= <span x-text="formatNum(discountAmount)"></span></span>
                            </div>
                            <input type="hidden" name="discount_amount" :value="discountAmount">
                        </div>

                        {{-- ضريبة --}}
                        <div>
                            <label class="text-gray-500">ضريبة %</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="number" name="tax_percent"
                                       x-model.number="taxPercent"
                                       @input="calcTotals()"
                                       min="0" max="100" step="0.01"
                                       class="w-20 border border-gray-200 rounded px-2 py-1 text-sm text-center focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                                <span class="text-gray-400 text-xs">= <span x-text="formatNum(taxAmount)"></span></span>
                            </div>
                            <input type="hidden" name="tax_amount" :value="taxAmount">
                        </div>

                        <div class="border-t pt-2 flex justify-between font-bold text-base">
                            <span>الإجمالي النهائي</span>
                            <span class="text-primary" x-text="formatNum(total)"></span>
                        </div>
                    </div>
                </div>

                {{-- زر الحفظ --}}
                <button type="submit"
                        class="w-full py-3 bg-primary text-white rounded-xl hover:bg-primary-dark transition font-semibold text-base shadow-lg shadow-primary/20">
                    <i class="fa fa-save me-2"></i> إصدار الفاتورة
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function invoiceForm() {
    return {
        items: [],
        invoiceType: 'cash',
        discountPercent: 0,
        taxPercent: 0,
        subtotal: 0,
        discountAmount: 0,
        taxAmount: 0,
        total: 0,

        init() {
            this.addItem();
        },

        addItem() {
            this.items.push({
                product_id: '',
                quantity: 1,
                unit_price: 0,
                discount_percent: 0,
                stock: 9999,
                unit: '',
                total: 0,
            });
        },

        removeItem(index) {
            if (this.items.length === 1) return;
            this.items.splice(index, 1);
            this.calcTotals();
        },

        onProductChange(index) {
            const item    = this.items[index];
            const select  = document.querySelectorAll('[name^="items[' + index + '][product_id]"]')[0];
            const opt     = select?.options[select.selectedIndex];
            if (!opt) return;
            item.unit_price = parseFloat(opt.dataset.price || 0);
            item.stock      = parseInt(opt.dataset.stock  || 9999);
            item.unit       = opt.dataset.unit || '';
            this.calcLine(index);
        },

        calcLine(index) {
            const item = this.items[index];
            const sub  = item.quantity * item.unit_price;
            const disc = item.discount_percent > 0
                ? Math.round(sub * item.discount_percent / 100 * 100) / 100
                : 0;
            item.total = Math.round((sub - disc) * 100) / 100;
            this.calcTotals();
        },

        calcTotals() {
            this.subtotal       = this.items.reduce((s, i) => s + (i.total || 0), 0);
            this.discountAmount = this.discountPercent > 0
                ? Math.round(this.subtotal * this.discountPercent / 100 * 100) / 100
                : 0;
            const afterDisc     = this.subtotal - this.discountAmount;
            this.taxAmount      = this.taxPercent > 0
                ? Math.round(afterDisc * this.taxPercent / 100 * 100) / 100
                : 0;
            this.total          = Math.round((afterDisc + this.taxAmount) * 100) / 100;
        },

        formatNum(n) {
            return new Intl.NumberFormat('ar-SD', { minimumFractionDigits: 2 }).format(n || 0);
        },
    };
}
</script>
@endpush
@endsection
