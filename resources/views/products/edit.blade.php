{{-- المسار الكامل: resources/views/products/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'تعديل: ' . $product->name_ar)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('products.show', $product) }}" class="btn-icon text-gray-500">
            <i class="fas fa-arrow-right"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">تعديل المنتج</h1>
            <p class="text-sm text-gray-500 font-mono">{{ $product->sku }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ─── البيانات الأساسية ─────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                <div class="card">
                    <h2 class="card-title">البيانات الأساسية</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="form-label">اسم المنتج بالعربية <span class="text-red-500">*</span></label>
                            <input type="text" name="name_ar"
                                   value="{{ old('name_ar', $product->name_ar) }}"
                                   class="input-field w-full @error('name_ar') border-red-400 @enderror" required>
                            @error('name_ar') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="form-label">اسم المنتج بالإنجليزية</label>
                            <input type="text" name="name_en"
                                   value="{{ old('name_en', $product->name_en) }}"
                                   class="input-field w-full" dir="ltr">
                        </div>

                        <div>
                            <label class="form-label">كود المنتج (SKU)</label>
                            <input type="text" name="sku"
                                   value="{{ old('sku', $product->sku) }}"
                                   class="input-field w-full font-mono">
                            @error('sku') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label">الباركود</label>
                            <input type="text" name="barcode"
                                   value="{{ old('barcode', $product->barcode) }}"
                                   class="input-field w-full font-mono" dir="ltr">
                        </div>

                        <div>
                            <label class="form-label">الفئة</label>
                            <select name="category_id" class="input-field w-full">
                                <option value="">اختر الفئة</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">نوع المنتج <span class="text-red-500">*</span></label>
                            <select name="type" class="input-field w-full" required>
                                @foreach(['power_tools' => 'أدوات كهربائية', 'hand_tools' => 'أدوات يدوية', 'equipment' => 'معدات', 'spare_parts' => 'قطع غيار', 'other' => 'أخرى'] as $val => $label)
                                <option value="{{ $val }}" {{ old('type', $product->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">العلامة التجارية</label>
                            <input type="text" name="brand"
                                   value="{{ old('brand', $product->brand) }}"
                                   class="input-field w-full">
                        </div>

                        <div>
                            <label class="form-label">وحدة القياس <span class="text-red-500">*</span></label>
                            <input type="text" name="unit"
                                   value="{{ old('unit', $product->unit) }}"
                                   class="input-field w-full" required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" rows="3"
                                  class="input-field w-full">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                {{-- الأسعار --}}
                <div class="card">
                    <h2 class="card-title">الأسعار</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">سعر الشراء <span class="text-red-500">*</span></label>
                            <input type="number" name="purchase_price" id="purchase_price"
                                   value="{{ old('purchase_price', $product->purchase_price) }}"
                                   class="input-field w-full" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label class="form-label">سعر البيع <span class="text-red-500">*</span></label>
                            <input type="number" name="sale_price" id="sale_price"
                                   value="{{ old('sale_price', $product->sale_price) }}"
                                   class="input-field w-full" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label class="form-label">هامش الربح</label>
                            <div class="input-field bg-gray-50 text-teal-700 font-bold">
                                <span id="profit_display">{{ $product->profit_margin }}</span>%
                            </div>
                        </div>
                        <div>
                            <label class="form-label">حد الطلب الأدنى</label>
                            <input type="number" name="reorder_point"
                                   value="{{ old('reorder_point', $product->reorder_point) }}"
                                   class="input-field w-full" min="0">
                        </div>
                    </div>
                </div>

            </div>

            {{-- ─── الصورة والحالة ───────────────────────────────────── --}}
            <div class="space-y-6">

                <div class="card">
                    <h2 class="card-title">الصورة</h2>
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center">
                        <img id="preview" src="{{ $product->image_url }}"
                             alt="{{ $product->name_ar }}"
                             class="w-full h-40 object-contain mb-3 rounded">
                        <label class="cursor-pointer">
                            <span class="btn-secondary text-sm">تغيير الصورة</span>
                            <input type="file" name="image" accept="image/*" class="hidden"
                                   onchange="previewImage(this)">
                        </label>
                    </div>
                </div>

                <div class="card">
                    <h2 class="card-title">الحالة</h2>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                               class="w-5 h-5 rounded text-teal-600">
                        <span class="text-gray-700">منتج نشط</span>
                    </label>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="btn-primary w-full justify-center">
                        <i class="fas fa-save ml-2"></i> حفظ التغييرات
                    </button>
                    <a href="{{ route('products.show', $product) }}" class="btn-secondary w-full text-center">
                        إلغاء
                    </a>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById('preview').src = e.target.result;
            reader.readAsDataURL(input.files[0]);
        }
    }
    function calcMargin() {
        const p = parseFloat(document.getElementById('purchase_price').value) || 0;
        const s = parseFloat(document.getElementById('sale_price').value) || 0;
        document.getElementById('profit_display').textContent =
            p > 0 ? (((s - p) / p) * 100).toFixed(2) : 0;
    }
    document.getElementById('purchase_price').addEventListener('input', calcMargin);
    document.getElementById('sale_price').addEventListener('input', calcMargin);
</script>
@endpush
@endsection
