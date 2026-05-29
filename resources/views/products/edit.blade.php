{{-- المسار الكامل: resources/views/products/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'تعديل: ' . $product->name_ar)

@push('styles')
<style>
    /* ── Hero ── */
    .edit-hero {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 50%, #115e59 100%);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(13, 148, 136, 0.3);
    }
    .edit-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 240px; height: 240px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .edit-hero::after {
        content: '';
        position: absolute;
        bottom: -80px; left: -40px;
        width: 180px; height: 180px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }

    /* ── البطاقات ── */
    .edit-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .edit-card-header {
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        border-bottom: 2px solid #0d9488;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .edit-card-header h2 {
        font-size: .9rem;
        font-weight: 700;
        color: #0f766e;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .edit-card-header i {
        color: #0d9488;
        font-size: 1rem;
        opacity: .8;
    }
    .edit-card-body {
        padding: 1.5rem;
    }

    /* ── حقول الإدخال ── */
    .edit-label {
        display: block;
        font-size: .8rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .edit-label .required { color: #e11d48; }
    .edit-input {
        width: 100%;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: .7rem 1rem;
        font-size: .9rem;
        outline: none;
        transition: all .2s;
        background: #f9fafb;
        font-family: 'Tajawal', sans-serif;
        color: #111827;
    }
    .edit-input:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1);
        background: #fff;
    }
    .edit-input.error { border-color: #f87171; background: #fff1f2; }
    .edit-error {
        font-size: .78rem;
        color: #e11d48;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .edit-input-readonly {
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        color: #0d9488;
        font-weight: 700;
        border-color: #99f6e4;
        cursor: default;
    }

    /* ── قسم الصورة ── */
    .image-upload-zone {
        border: 2px dashed #d1d5db;
        border-radius: 14px;
        padding: 1.5rem;
        text-align: center;
        transition: all .2s;
        cursor: pointer;
        background: #fafafa;
        position: relative;
        overflow: hidden;
    }
    .image-upload-zone:hover {
        border-color: #0d9488;
        background: #f0fdfa;
    }
    .image-upload-zone img {
        width: 100%;
        height: 160px;
        object-fit: contain;
        border-radius: 10px;
        margin-bottom: 12px;
    }
    .image-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        color: #0d9488;
        border: 2px solid #99f6e4;
        border-radius: 10px;
        padding: .5rem 1.2rem;
        font-size: .85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s;
    }
    .image-upload-btn:hover {
        background: linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
    }

    /* ── toggle الحالة ── */
    .toggle-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: all .2s;
    }
    .toggle-wrap:has(input:checked) {
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        border-color: #99f6e4;
    }
    .toggle-wrap input[type="checkbox"] {
        width: 20px; height: 20px;
        accent-color: #0d9488;
        cursor: pointer;
    }
    .toggle-label { font-size: .9rem; color: #374151; font-weight: 600; }

    /* ── هامش الربح ── */
    .profit-display {
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        border: 2px solid #99f6e4;
        border-radius: 12px;
        padding: .7rem 1rem;
        font-weight: 800;
        font-size: 1.1rem;
        color: #0d9488;
        text-align: center;
        letter-spacing: -.01em;
    }
    .profit-display.negative {
        background: linear-gradient(135deg, #fff1f2 0%, #fecdd3 100%);
        border-color: #fda4af;
        color: #e11d48;
    }

    /* ── أزرار الحفظ ── */
    .btn-save {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        color: white;
        font-weight: 700;
        font-size: .95rem;
        padding: .85rem 1.5rem;
        border-radius: 14px;
        border: none;
        cursor: pointer;
        width: 100%;
        transition: all .2s;
        box-shadow: 0 4px 16px rgba(13, 148, 136, 0.3);
        font-family: 'Tajawal', sans-serif;
    }
    .btn-save:hover {
        background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
        box-shadow: 0 8px 24px rgba(13, 148, 136, 0.4);
        transform: translateY(-1px);
    }
    .btn-save:active { transform: translateY(0); }

    .btn-cancel {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #fff;
        color: #6b7280;
        font-weight: 600;
        font-size: .9rem;
        padding: .8rem 1.5rem;
        border-radius: 14px;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        width: 100%;
        transition: all .2s;
        text-decoration: none;
        font-family: 'Tajawal', sans-serif;
    }
    .btn-cancel:hover {
        border-color: #fda4af;
        color: #e11d48;
        background: #fff1f2;
    }

    /* ── SKU badge في الهيدر ── */
    .sku-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 10px;
        padding: .3rem .85rem;
        font-size: .8rem;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        letter-spacing: .06em;
        color: rgba(255,255,255,0.9);
        backdrop-filter: blur(4px);
    }

    /* ── back button ── */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        font-weight: 600;
        font-size: .85rem;
        padding: .5rem 1rem;
        border-radius: 10px;
        text-decoration: none;
        transition: all .2s;
        backdrop-filter: blur(4px);
    }
    .btn-back:hover {
        background: rgba(255,255,255,0.25);
        border-color: rgba(255,255,255,0.5);
        color: white;
    }

    /* ── info strip ── */
    .info-strip {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: .75rem 1rem;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border: 1px solid #fde68a;
        border-radius: 10px;
        font-size: .8rem;
        color: #92400e;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto space-y-5">

    {{-- ─── Hero Header ─────────────────────────────────────────────────── --}}
    <div class="edit-hero">
        <div class="flex items-center justify-between relative z-10">
            <div class="flex items-start gap-4">
                <a href="{{ route('products.show', $product) }}" class="btn-back mt-1">
                    <i class="fas fa-arrow-right text-xs"></i>
                    عودة
                </a>
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-pen-to-square text-white/70 text-xl"></i>
                        <h1 class="text-2xl font-bold text-white tracking-tight">تعديل المنتج</h1>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="sku-hero-badge">
                            <i class="fas fa-barcode text-xs"></i>
                            {{ $product->sku }}
                        </span>
                        <span class="text-white/60 text-sm">{{ $product->name_ar }}</span>
                    </div>
                </div>
            </div>
            {{-- حالة المنتج في الهيدر --}}
            <div class="text-right hidden md:block">
                @if($product->is_active)
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:24px;padding:.35rem 1rem;font-size:.8rem;font-weight:700;color:white;">
                    <span style="width:7px;height:7px;border-radius:50%;background:#4ade80;box-shadow:0 0 8px #4ade80;display:inline-block;"></span>
                    منتج نشط
                </span>
                @else
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:24px;padding:.35rem 1rem;font-size:.8rem;font-weight:700;color:white;">
                    <span style="width:7px;height:7px;border-radius:50%;background:#f87171;box-shadow:0 0 8px #f87171;display:inline-block;"></span>
                    موقوف
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── النموذج ──────────────────────────────────────────────────────── --}}
    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- ═══ العمود الرئيسي (2/3) ═══ --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- ── البيانات الأساسية ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <i class="fas fa-layer-group"></i>
                        <h2>البيانات الأساسية</h2>
                    </div>
                    <div class="edit-card-body">

                        {{-- اسم المنتج بالعربية --}}
                        <div class="mb-4">
                            <label class="edit-label">اسم المنتج بالعربية <span class="required">*</span></label>
                            <input type="text" name="name_ar"
                                   value="{{ old('name_ar', $product->name_ar) }}"
                                   class="edit-input @error('name_ar') error @enderror"
                                   placeholder="أدخل الاسم بالعربية..."
                                   required>
                            @error('name_ar')
                                <p class="edit-error"><i class="fas fa-circle-exclamation text-xs"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        {{-- اسم المنتج بالإنجليزية --}}
                        <div class="mb-4">
                            <label class="edit-label">اسم المنتج بالإنجليزية</label>
                            <input type="text" name="name_en"
                                   value="{{ old('name_en', $product->name_en) }}"
                                   class="edit-input"
                                   placeholder="Product name in English..."
                                   dir="ltr">
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            {{-- SKU --}}
                            <div>
                                <label class="edit-label">كود المنتج (SKU)</label>
                                <input type="text" name="sku"
                                       value="{{ old('sku', $product->sku) }}"
                                       class="edit-input font-mono @error('sku') error @enderror"
                                       placeholder="مثال: PRD-001"
                                       dir="ltr">
                                @error('sku')
                                    <p class="edit-error"><i class="fas fa-circle-exclamation text-xs"></i> {{ $message }}</p>
                                @enderror
                            </div>
                            {{-- الباركود --}}
                            <div>
                                <label class="edit-label">الباركود</label>
                                <input type="text" name="barcode"
                                       value="{{ old('barcode', $product->barcode) }}"
                                       class="edit-input font-mono"
                                       placeholder="6291041500213"
                                       dir="ltr">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            {{-- الفئة --}}
                            <div>
                                <label class="edit-label">الفئة</label>
                                <select name="category_id" class="edit-input">
                                    <option value="">— اختر الفئة —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name_ar }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- نوع المنتج --}}
                            <div>
                                <label class="edit-label">نوع المنتج <span class="required">*</span></label>
                                <select name="type" class="edit-input" required>
                                    @foreach([
                                        'power_tools' => 'أدوات كهربائية',
                                        'hand_tools'  => 'أدوات يدوية',
                                        'equipment'   => 'معدات',
                                        'spare_parts' => 'قطع غيار',
                                        'other'       => 'أخرى'
                                    ] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('type', $product->type) === $val ? 'selected' : '' }}>
                                        {{ $lbl }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            {{-- العلامة التجارية --}}
                            <div>
                                <label class="edit-label">العلامة التجارية</label>
                                <input type="text" name="brand"
                                       value="{{ old('brand', $product->brand) }}"
                                       class="edit-input"
                                       placeholder="مثال: Bosch, Makita...">
                            </div>
                            {{-- وحدة القياس --}}
                            <div>
                                <label class="edit-label">وحدة القياس <span class="required">*</span></label>
                                <input type="text" name="unit"
                                       value="{{ old('unit', $product->unit) }}"
                                       class="edit-input"
                                       placeholder="مثال: قطعة، كجم، متر..."
                                       required>
                            </div>
                        </div>

                        {{-- الوصف --}}
                        <div>
                            <label class="edit-label">الوصف</label>
                            <textarea name="description" rows="3"
                                      class="edit-input"
                                      placeholder="وصف تفصيلي للمنتج...">{{ old('description', $product->description) }}</textarea>
                        </div>

                    </div>
                </div>

                {{-- ── الأسعار ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <i class="fas fa-tags"></i>
                        <h2>الأسعار والمخزون</h2>
                    </div>
                    <div class="edit-card-body">

                        {{-- ★ سعر الصرف الحالي --}}
                        @php $currentRate = \App\Models\ExchangeRate::currentRate(); @endphp
                        @if($currentRate)
                        <div class="mb-4 flex items-center gap-2 bg-teal-50 border border-teal-200 rounded-lg px-4 py-2 text-sm text-teal-700">
                            <i class="fas fa-exchange-alt"></i>
                            سعر الصرف الحالي: <strong>1 USD = {{ number_format($currentRate, 0) }} ج.س</strong>
                            <span class="text-teal-400 text-xs mr-auto">يُحسب تلقائياً عند الإدخال</span>
                        </div>
                        @else
                        <div class="mb-4 flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-triangle"></i>
                            لم يُحدَّد سعر الصرف —
                            <a href="{{ route('exchange-rates.index') }}" class="underline font-medium">حدده الآن</a>
                        </div>
                        @endif

                        <div class="grid grid-cols-3 gap-4 mb-4">
                            {{-- سعر الشراء USD --}}
                            <div>
                                <label class="edit-label">سعر الشراء (USD) <span class="required">*</span></label>
                                <div class="relative">
                                    <input type="number" name="purchase_price_usd" id="purchase_price_usd"
                                           value="{{ old('purchase_price_usd', $product->purchase_price_usd ?? round($product->purchase_price / ($currentRate ?: 1), 2)) }}"
                                           class="edit-input"
                                           style="padding-left:2.5rem;"
                                           step="0.01" min="0" required>
                                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:.8rem;color:#16a34a;font-weight:700;">$</span>
                                </div>
                                <div class="mt-1 text-xs text-gray-400 flex items-center gap-1">
                                    <i class="fas fa-arrow-left text-[10px]"></i>
                                    <span id="purchase_sdg_preview">{{ number_format($product->purchase_price, 0) }} ج.س</span>
                                </div>
                                <input type="hidden" name="purchase_price" id="purchase_price" value="{{ $product->purchase_price }}">
                            </div>
                            {{-- سعر البيع USD --}}
                            <div>
                                <label class="edit-label">سعر البيع (USD) <span class="required">*</span></label>
                                <div class="relative">
                                    <input type="number" name="price_usd" id="price_usd"
                                           value="{{ old('price_usd', $product->price_usd ?? round($product->sale_price / ($currentRate ?: 1), 2)) }}"
                                           class="edit-input"
                                           style="padding-left:2.5rem;"
                                           step="0.01" min="0" required>
                                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:.8rem;color:#16a34a;font-weight:700;">$</span>
                                </div>
                                <div class="mt-1 text-xs text-gray-400 flex items-center gap-1">
                                    <i class="fas fa-arrow-left text-[10px]"></i>
                                    <span id="sale_sdg_preview">{{ number_format($product->sale_price, 0) }} ج.س</span>
                                </div>
                                <input type="hidden" name="sale_price" id="sale_price" value="{{ $product->sale_price }}">
                            </div>
                            {{-- هامش الربح --}}
                            <div>
                                <label class="edit-label">هامش الربح</label>
                                <div class="profit-display" id="profit_display">
                                    {{ $product->profit_margin }}%
                                </div>
                            </div>
                        </div>

                        {{-- حد إعادة الطلب --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="edit-label">حد الطلب الأدنى</label>
                                <input type="number" name="reorder_point"
                                       value="{{ old('reorder_point', $product->reorder_point) }}"
                                       class="edit-input"
                                       placeholder="0"
                                       min="0">
                            </div>
                            <div>
                                <label class="edit-label">المخزون الحالي</label>
                                <div class="edit-input edit-input-readonly flex items-center justify-between">
                                    <span>{{ number_format($product->quantity) }} {{ $product->unit }}</span>
                                    <i class="fas fa-lock text-xs opacity-50"></i>
                                </div>
                            </div>
                        </div>

                        {{-- تنبيه --}}
                        <div class="info-strip mt-4">
                            <i class="fas fa-circle-info"></i>
                            لتعديل المخزون، استخدم قسم حركة المخزون بعد الحفظ.
                        </div>

                    </div>
                </div>

            </div>

            {{-- ═══ العمود الجانبي (1/3) ═══ --}}
            <div class="space-y-5">

                {{-- ── الصورة ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <i class="fas fa-image"></i>
                        <h2>صورة المنتج</h2>
                    </div>
                    <div class="edit-card-body">
                        <label class="image-upload-zone" for="image-input">
                            <img id="preview"
                                 src="{{ $product->image_url }}"
                                 alt="{{ $product->name_ar }}"
                                 onerror="this.src='{{ asset('images/no-product.png') }}'">
                            <span class="image-upload-btn">
                                <i class="fas fa-camera text-xs"></i>
                                تغيير الصورة
                            </span>
                            <p style="font-size:.75rem;color:#9ca3af;margin-top:8px;">PNG، JPG — بحد أقصى 2MB</p>
                            <input type="file" id="image-input" name="image"
                                   accept="image/*" class="hidden"
                                   onchange="previewImage(this)">
                        </label>
                    </div>
                </div>

                {{-- ── الحالة ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <i class="fas fa-toggle-on"></i>
                        <h2>حالة المنتج</h2>
                    </div>
                    <div class="edit-card-body">
                        <label class="toggle-wrap">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <div>
                                <div class="toggle-label">منتج نشط</div>
                                <div style="font-size:.78rem;color:#9ca3af;margin-top:2px;">يظهر في قوائم البيع والمخزون</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- ── معلومات سريعة ── --}}
                <div class="edit-card">
                    <div class="edit-card-header">
                        <i class="fas fa-clock-rotate-left"></i>
                        <h2>معلومات التسجيل</h2>
                    </div>
                    <div class="edit-card-body">
                        <div class="space-y-3">
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem .75rem;background:#f9fafb;border-radius:10px;border:1px solid #f3f4f6;">
                                <span style="font-size:.78rem;color:#9ca3af;font-weight:600;">تاريخ الإضافة</span>
                                <span style="font-size:.82rem;color:#374151;font-weight:600;">{{ $product->created_at->format('Y/m/d') }}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem .75rem;background:#f9fafb;border-radius:10px;border:1px solid #f3f4f6;">
                                <span style="font-size:.78rem;color:#9ca3af;font-weight:600;">آخر تعديل</span>
                                <span style="font-size:.82rem;color:#374151;font-weight:600;">{{ $product->updated_at->format('Y/m/d') }}</span>
                            </div>
                            @if($product->category)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem .75rem;background:#f9fafb;border-radius:10px;border:1px solid #f3f4f6;">
                                <span style="font-size:.78rem;color:#9ca3af;font-weight:600;">الفئة الحالية</span>
                                <span style="font-size:.82rem;color:#0d9488;font-weight:700;">{{ $product->category->name_ar }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── أزرار الحفظ ── --}}
                <div class="space-y-3">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i>
                        حفظ التغييرات
                    </button>
                    <a href="{{ route('products.show', $product) }}" class="btn-cancel">
                        <i class="fas fa-times text-xs"></i>
                        إلغاء
                    </a>
                </div>

            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function calcMargin() {
    var rate = {{ $currentRate ?? 0 }};
    var p    = parseFloat(document.getElementById('purchase_price_usd').value) || 0;
    var s    = parseFloat(document.getElementById('price_usd').value) || 0;
    var el   = document.getElementById('profit_display');

    if (rate > 0) {
        document.getElementById('purchase_sdg_preview').textContent =
            Math.round(p * rate).toLocaleString('en') + ' ج.س';
        document.getElementById('sale_sdg_preview').textContent =
            Math.round(s * rate).toLocaleString('en') + ' ج.س';
        document.getElementById('purchase_price').value = (p * rate).toFixed(2);
        document.getElementById('sale_price').value     = (s * rate).toFixed(2);
    }

    if (p > 0) {
        var margin = ((s - p) / p * 100).toFixed(2);
        el.textContent = margin + '%';
        el.classList.toggle('negative', parseFloat(margin) < 0);
    } else {
        el.textContent = '0%';
        el.classList.remove('negative');
    }
}

document.getElementById('purchase_price_usd').addEventListener('input', calcMargin);
document.getElementById('price_usd').addEventListener('input', calcMargin);
</script>
@endpush
