{{-- المسار الكامل: resources/views/products/create.blade.php --}}

@extends('layouts.app')

@section('title', 'إضافة منتج جديد')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('products.index') }}" class="btn-icon text-gray-500">
            <i class="fas fa-arrow-right"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">إضافة منتج جديد</h1>
            <p class="text-sm text-gray-500">كود تلقائي مقترح: <span class="font-mono text-teal-600">{{ $nextSku }}</span></p>
        </div>
    </div>

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ─── البيانات الأساسية ─────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                <div class="card">
                    <h2 class="card-title">البيانات الأساسية</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="form-label">اسم المنتج بالعربية <span class="text-red-500">*</span></label>
                            <input type="text" name="name_ar" value="{{ old('name_ar') }}"
                                   class="input-field w-full @error('name_ar') border-red-400 @enderror"
                                   placeholder="مثال: مثقاب كهربائي 750W" required>
                            @error('name_ar') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="form-label">اسم المنتج بالإنجليزية</label>
                            <input type="text" name="name_en" value="{{ old('name_en') }}"
                                   class="input-field w-full" dir="ltr"
                                   placeholder="e.g. Electric Drill 750W">
                        </div>

                        <div>
                            <label class="form-label">كود المنتج (SKU)</label>
                            <input type="text" name="sku" value="{{ old('sku', $nextSku) }}"
                                   class="input-field w-full font-mono"
                                   placeholder="{{ $nextSku }}">
                            @error('sku') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">الباركود</label>
                            <input type="text" name="barcode" value="{{ old('barcode') }}"
                                   class="input-field w-full font-mono" dir="ltr"
                                   placeholder="اختياري">
                            @error('barcode') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">الفئة</label>
                            <select name="category_id" class="input-field w-full">
                                <option value="">اختر الفئة</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">نوع المنتج <span class="text-red-500">*</span></label>
                            <select name="type" class="input-field w-full" required>
                                <option value="power_tools" {{ old('type') === 'power_tools' ? 'selected' : '' }}>أدوات كهربائية</option>
                                <option value="hand_tools"  {{ old('type') === 'hand_tools'  ? 'selected' : '' }}>أدوات يدوية</option>
                                <option value="equipment"   {{ old('type') === 'equipment'   ? 'selected' : '' }}>معدات</option>
                                <option value="spare_parts" {{ old('type') === 'spare_parts' ? 'selected' : '' }}>قطع غيار</option>
                                <option value="other"       {{ old('type') === 'other'       ? 'selected' : '' }}>أخرى</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">العلامة التجارية</label>
                            <input type="text" name="brand" value="{{ old('brand') }}"
                                   class="input-field w-full" placeholder="مثال: Total, Bosch">
                        </div>

                        <div>
                            <label class="form-label">وحدة القياس <span class="text-red-500">*</span></label>
                            <input type="text" name="unit" value="{{ old('unit', 'قطعة') }}"
                                   class="input-field w-full" required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" rows="3"
                                  class="input-field w-full"
                                  placeholder="وصف تفصيلي للمنتج...">{{ old('description') }}</textarea>
                    </div>
                </div>

                {{-- ─── الأسعار ────────────────────────────────────────── --}}
                <div class="card">
                    <h2 class="card-title">الأسعار والمخزون</h2>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">سعر الشراء <span class="text-red-500">*</span></label>
                            <input type="number" name="purchase_price" value="{{ old('purchase_price', 0) }}"
                                   class="input-field w-full" step="0.01" min="0" id="purchase_price" required>
                            @error('purchase_price') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">سعر البيع <span class="text-red-500">*</span></label>
                            <input type="number" name="sale_price" value="{{ old('sale_price', 0) }}"
                                   class="input-field w-full" step="0.01" min="0" id="sale_price" required>
                            @error('sale_price') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">هامش الربح</label>
                            <div class="input-field w-full bg-gray-50 flex items-center gap-1 text-teal-700 font-bold">
                                <span id="profit_display">0</span>%
                            </div>
                        </div>

                        <div>
                            <label class="form-label">حد الطلب الأدنى</label>
                            <input type="number" name="reorder_point" value="{{ old('reorder_point', 5) }}"
                                   class="input-field w-full" min="0">
                        </div>

                        <div>
                            <label class="form-label">مخزون ابتدائي</label>
                            <input type="number" name="initial_quantity" value="{{ old('initial_quantity', 0) }}"
                                   class="input-field w-full" min="0">
                        </div>

                        <div>
                            <label class="form-label">المستودع</label>
                            <select name="warehouse_id" class="input-field w-full">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ $wh->is_default ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ─── الصورة والحالة ───────────────────────────────────── --}}
            <div class="space-y-6">

                <div class="card">
                    <h2 class="card-title">الصورة الرئيسية</h2>
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center hover:border-teal-400 transition-colors">
                        <img id="preview" src="{{ asset('images/no-product.png') }}"
                             alt="معاينة" class="w-full h-40 object-contain mb-3 rounded">
                        <label class="cursor-pointer">
                            <span class="btn-secondary text-sm">اختر صورة</span>
                            <input type="file" name="image" accept="image/*" class="hidden"
                                   onchange="previewImage(this)">
                        </label>
                        <p class="text-xs text-gray-400 mt-2">JPG, PNG — حد أقصى 3MB</p>
                    </div>
                    @error('image') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="card">
                    <h2 class="card-title">الحالة</h2>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded text-teal-600">
                        <span class="text-gray-700">منتج نشط</span>
                    </label>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="btn-primary w-full justify-center">
                        <i class="fas fa-save ml-2"></i> حفظ المنتج
                    </button>
                    <a href="{{ route('products.index') }}" class="btn-secondary w-full text-center">
                        إلغاء
                    </a>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
    // معاينة الصورة
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById('preview').src = e.target.result;
            reader.readAsDataURL(input.files[0]);
        }
    }

    // حساب هامش الربح تلقائياً
    function calcMargin() {
        const purchase = parseFloat(document.getElementById('purchase_price').value) || 0;
        const sale     = parseFloat(document.getElementById('sale_price').value) || 0;
        const margin   = purchase > 0 ? (((sale - purchase) / purchase) * 100).toFixed(2) : 0;
        document.getElementById('profit_display').textContent = margin;
    }

    document.getElementById('purchase_price').addEventListener('input', calcMargin);
    document.getElementById('sale_price').addEventListener('input', calcMargin);
</script>
@endpush
@endsection
