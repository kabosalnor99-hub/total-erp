{{-- المسار الكامل: resources/views/products/index.blade.php --}}

@extends('layouts.app')

@section('title', 'المنتجات')

@push('styles')
<style>
    .products-hero {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 50%, #115e59 100%);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(13, 148, 136, 0.3);
    }
    .products-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 240px; height: 240px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
        backdrop-filter: blur(10px);
    }
    .products-hero::after {
        content: '';
        position: absolute;
        bottom: -80px; left: -40px;
        width: 180px; height: 180px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        backdrop-filter: blur(10px);
    }

    /* ── بطاقات الإحصائيات ── */
    .stat-tile {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .stat-tile:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        transform: translateY(-4px);
    }
    .stat-tile.active { 
        border-color: #0d9488; 
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.15), 0 8px 30px rgba(13, 148, 136, 0.2); 
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
    }
    .stat-tile-icon {
        width: 56px; height: 56px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .stat-tile-value { font-size: 2rem; font-weight: 800; line-height: 1; letter-spacing: -0.02em; }
    .stat-tile-label { font-size: .85rem; color: #6b7280; margin-top: 4px; font-weight: 500; }

    /* ── شريط الفلاتر ── */
    .filter-bar {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .filter-input {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: .7rem 1rem;
        font-size: .9rem;
        width: 100%;
        outline: none;
        transition: all .2s;
        background: #f9fafb;
        font-family: 'Tajawal', sans-serif;
    }
    .filter-input:focus { 
        border-color: #0d9488; 
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1); 
        background:#fff; 
    }

    /* ── الجدول ── */
    .products-table-wrap {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .products-table {
        width: 100%;
        border-collapse: collapse;
    }
    .products-table thead tr {
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        border-bottom: 2px solid #0d9488;
    }
    .products-table thead th {
        padding: 1rem 1.25rem;
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #0f766e;
        text-align: right;
        white-space: nowrap;
    }
    .products-table thead th:first-child { padding-right: 1.5rem; }
    .products-table thead th:last-child  { padding-left: 1.5rem;  }

    .products-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: all .2s;
    }
    .products-table tbody tr:last-child { border-bottom: none; }
    .products-table tbody tr:hover { 
        background: linear-gradient(135deg, #f0fdfa 0%, #f0fdf4 100%);
        transform: scale(1.005);
    }

    .products-table tbody td {
        padding: 1rem 1.25rem;
        font-size: .9rem;
        color: #374151;
        vertical-align: middle;
        text-align: right;
    }
    .products-table tbody td:first-child { padding-right: 1.5rem; }
    .products-table tbody td:last-child  { padding-left: 1.5rem;  }

    /* ── صورة + اسم المنتج ── */
    .product-name-cell { display: flex; align-items: center; gap: 1rem; }
    .product-thumb {
        width: 52px; height: 52px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid #e5e7eb;
        flex-shrink: 0;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .product-name-ar { font-weight: 700; color: #111827; font-size: .95rem; line-height: 1.4; }
    .product-name-en { font-size: .8rem; color: #9ca3af; margin-top: 2px; }

    /* ── SKU badge ── */
    .sku-badge {
        display: inline-block;
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        color: #0d9488;
        border: 2px solid #99f6e4;
        border-radius: 10px;
        padding: .35rem .75rem;
        font-size: .8rem;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        letter-spacing: .04em;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(13, 148, 136, 0.15);
    }

    /* ── سعر ── */
    .price-purchase { color: #6b7280; font-size: .9rem; font-weight: 500; }
    .price-sale { 
        color: #0d9488; 
        font-weight: 800; 
        font-size: 1.05rem; 
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        padding: .25rem .6rem;
        border-radius: 8px;
        border: 1px solid #99f6e4;
    }

    /* ── المخزون ── */
    .stock-pill {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .35rem .75rem;
        border-radius: 10px;
        font-size: .85rem;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .stock-green  { 
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); 
        color: #166534; 
        border: 1px solid #86efac; 
    }
    .stock-yellow { 
        background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%); 
        color: #854d0e; 
        border: 1px solid #fde047; 
    }
    .stock-red    { 
        background: linear-gradient(135deg, #fff1f2 0%, #fecdd3 100%); 
        color: #9f1239; 
        border: 1px solid #fda4af; 
    }
    .stock-gray   { 
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); 
        color: #6b7280; 
        border: 1px solid #d1d5db; 
    }

    /* ── الحالة ── */
    .status-active   { 
        display:inline-flex; align-items:center; gap:.4rem; 
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); 
        color:#065f46; 
        border: 1px solid #6ee7b7; 
        border-radius:24px; 
        padding:.3rem .85rem; 
        font-size:.8rem; 
        font-weight:700; 
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.15);
    }
    .status-inactive { 
        display:inline-flex; align-items:center; gap:.4rem; 
        background: linear-gradient(135deg, #fff1f2 0%, #fecdd3 100%); 
        color:#9f1239; 
        border: 1px solid #fda4af; 
        border-radius:24px; 
        padding:.3rem .85rem; 
        font-size:.8rem; 
        font-weight:700; 
        box-shadow: 0 2px 8px rgba(244, 63, 94, 0.15);
    }
    .status-dot { width:7px; height:7px; border-radius:50%; box-shadow: 0 0 8px currentColor; }
    .dot-green  { background: #10b981; }
    .dot-red    { background: #f43f5e; }

    /* ── أزرار الإجراءات ── */
    .action-btn {
        width: 38px; height: 38px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px;
        border: 2px solid transparent;
        font-size: 1rem;
        transition: all .2s;
        color: #9ca3af;
        cursor: pointer;
        background: none;
    }
    .action-btn:hover { 
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .action-btn.view:hover  { 
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); 
        border-color: #99f6e4; 
        color: #0d9488; 
    }
    .action-btn.edit:hover  { 
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); 
        border-color: #93c5fd; 
        color: #2563eb; 
    }
    .action-btn.del:hover   { 
        background: linear-gradient(135deg, #fff1f2 0%, #fecdd3 100%); 
        border-color: #fda4af; 
        color: #e11d48; 
    }

    /* ── فارغ ── */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        color: #9ca3af;
    }
    .empty-state i { 
        font-size: 4rem; 
        display: block; 
        margin-bottom: 1.5rem; 
        opacity: .3;
        background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .empty-state p { font-size: 1rem; }

    /* ── pagination ── */
    .pagination-wrap {
        padding: 1.25rem 2rem;
        border-top: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    }

    /* ── تسليط الضوء على صف محدد ── */
    @keyframes rowHighlight {
        from { background: linear-gradient(135deg, #ccfbf1 0%, #d1fae5 100%); }
        to   { background: transparent; }
    }
    .row-flash { animation: rowHighlight 1.5s ease-out; }
</style>
@endpush

@section('content')
<div class="space-y-5">

    {{-- ─── Hero Header ─────────────────────────────────────────────────── --}}
    <div class="products-hero">
        <div class="flex items-center justify-between relative z-10">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <i class="fas fa-boxes-stacked text-white/70 text-xl"></i>
                    <h1 class="text-2xl font-bold text-white tracking-tight">إدارة المنتجات</h1>
                </div>
                <p class="text-white/60 text-sm">
                    إجمالي المنتجات:
                    <span class="text-white font-semibold">{{ number_format($stats['total']) }}</span>
                </p>
            </div>
            @if(auth()->user()->hasPermission('products.create'))
            <a href="{{ route('products.create') }}"
               class="flex items-center gap-2 bg-white text-[#0D5050] font-semibold text-sm px-4 py-2.5 rounded-xl
                      hover:bg-[#e0f0f0] transition shadow-sm">
                <i class="fas fa-plus text-xs"></i>
                إضافة منتج جديد
            </a>
            @endif
        </div>
    </div>

    {{-- ─── بطاقات الإحصائيات ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        <a href="{{ route('products.index') }}"
           class="stat-tile {{ !request()->has('status') && !request()->has('filter') ? 'active' : '' }}">
            <div class="stat-tile-icon" style="background:#e0f0f0; color:#146E6E;">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <div class="stat-tile-value" style="color:#146E6E;">{{ number_format($stats['total']) }}</div>
                <div class="stat-tile-label">إجمالي المنتجات</div>
            </div>
        </a>

        <a href="{{ route('products.index', ['status' => 'active']) }}"
           class="stat-tile {{ request('status') === 'active' ? 'active' : '' }}">
            <div class="stat-tile-icon" style="background:#d1fae5; color:#059669;">
                <i class="fas fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-tile-value" style="color:#059669;">{{ number_format($stats['active']) }}</div>
                <div class="stat-tile-label">منتجات نشطة</div>
            </div>
        </a>

        <a href="{{ route('products.index', ['filter' => 'critical']) }}"
           class="stat-tile {{ request('filter') === 'critical' ? 'active' : '' }}">
            <div class="stat-tile-icon" style="background:#fef9c3; color:#d97706;">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="stat-tile-value" style="color:#d97706;">{{ number_format($stats['critical']) }}</div>
                <div class="stat-tile-label">مخزون حرج</div>
            </div>
        </a>

        <a href="{{ route('products.index', ['filter' => 'out_of_stock']) }}"
           class="stat-tile {{ request('filter') === 'out_of_stock' ? 'active' : '' }}">
            <div class="stat-tile-icon" style="background:#ffe4e6; color:#e11d48;">
                <i class="fas fa-box-open"></i>
            </div>
            <div>
                <div class="stat-tile-value" style="color:#e11d48;">{{ number_format($stats['out_of_stock']) }}</div>
                <div class="stat-tile-label">نفد المخزون</div>
            </div>
        </a>

    </div>

    {{-- ─── شريط الفلاتر ──────────────────────────────────────────────── --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('products.index') }}"
              class="flex flex-wrap md:flex-nowrap items-end gap-3">

            {{-- البحث --}}
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-400 mb-1.5 font-medium">بحث</label>
                <div class="relative">
                    <i class="fas fa-search absolute top-1/2 -translate-y-1/2 right-3 text-gray-400 text-xs pointer-events-none"></i>
                    <input type="text" name="search" id="live-search" value="{{ request('search') }}"
                           placeholder="اسم المنتج، الكود، الباركود..."
                           class="filter-input pr-8"
                           autocomplete="off">

                </div>
            </div>

            {{-- الفئة --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs text-gray-400 mb-1.5 font-medium">الفئة</label>
                <select name="category_id" class="filter-input">
                    <option value="">كل الفئات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- النوع --}}
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs text-gray-400 mb-1.5 font-medium">النوع</label>
                <select name="type" class="filter-input">
                    <option value="">كل الأنواع</option>
                    <option value="power_tools" {{ request('type') === 'power_tools' ? 'selected' : '' }}>أدوات كهربائية</option>
                    <option value="hand_tools"  {{ request('type') === 'hand_tools'  ? 'selected' : '' }}>أدوات يدوية</option>
                    <option value="equipment"   {{ request('type') === 'equipment'   ? 'selected' : '' }}>معدات</option>
                    <option value="spare_parts" {{ request('type') === 'spare_parts' ? 'selected' : '' }}>قطع غيار</option>
                    <option value="other"       {{ request('type') === 'other'       ? 'selected' : '' }}>أخرى</option>
                </select>
            </div>

            {{-- أزرار --}}
            <div class="flex gap-2 flex-shrink-0">
                <button type="submit"
                        class="flex items-center gap-2 bg-[#146E6E] hover:bg-[#0D5050] text-white
                               font-semibold text-sm px-4 py-2 rounded-xl transition">
                    <i class="fas fa-search text-xs"></i>
                    بحث
                </button>
                <a href="{{ route('products.index') }}"
                   class="flex items-center justify-center w-10 h-[38px] border border-gray-200
                          rounded-xl text-gray-400 hover:border-red-300 hover:text-red-400
                          hover:bg-red-50 transition"
                   title="مسح الفلاتر">
                    <i class="fas fa-times text-sm"></i>
                </a>
            </div>

        </form>
    </div>

    {{-- ─── نتائج البحث المباشر (تظهر تحت شريط الفلاتر بعرض كامل) ────── --}}
    <div id="search-results"
         class="hidden"
         style="
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            z-index: 9998;
            pointer-events: none;
         ">
        <div id="search-results-panel"
             style="
                position: absolute;
                background: #fff;
                border: 1.5px solid #e5e7eb;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 4px 16px rgba(13,148,136,0.1);
                max-height: 480px;
                overflow-y: auto;
                pointer-events: all;
                z-index: 9999;
             ">
        </div>
    </div>

    {{-- ─── جدول المنتجات ─────────────────────────────────────────────── --}}
    <div class="products-table-wrap">

        {{-- رأس الجدول: عدد النتائج --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-white">
            <span class="text-xs text-gray-400 font-medium">
                عرض {{ $products->count() }} من {{ $products->total() }} منتج
            </span>
            @if(request()->hasAny(['search','category_id','type','status','filter']))
            <a href="{{ route('products.index') }}"
               class="text-xs text-[#146E6E] hover:underline flex items-center gap-1">
                <i class="fas fa-filter-circle-xmark"></i>
                إزالة الفلاتر
            </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="products-table">
                <thead>
                    <tr>
                        <th style="min-width:220px">المنتج</th>
                        <th>الكود (SKU)</th>
                        <th>الفئة</th>
                        <th>سعر الشراء</th>
                        <th>سعر البيع</th>
                        <th>المخزون</th>
                        <th>الحالة</th>
                        <th style="text-align:center; min-width:110px">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr id="row-{{ $product->id }}">

                        {{-- المنتج --}}
                        <td>
                            <div class="product-name-cell">
                                <img src="{{ $product->image_url }}"
                                     alt="{{ $product->name_ar }}"
                                     class="product-thumb"
                                     onerror="this.src='{{ asset('images/no-product.png') }}'">
                                <div>
                                    <div class="product-name-ar">{{ $product->name_ar }}</div>
                                    @if($product->name_en)
                                    <div class="product-name-en">{{ $product->name_en }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- الكود --}}
                        <td>
                            <span class="sku-badge">{{ $product->sku }}</span>
                        </td>

                        {{-- الفئة --}}
                        <td class="text-gray-500">
                            {{ $product->category?->name_ar ?? '—' }}
                        </td>

                        {{-- سعر الشراء --}}
                        <td>
                            <span class="price-purchase">{{ number_format($product->purchase_price, 2) }}</span>
                        </td>

                        {{-- سعر البيع --}}
                        <td>
                            <span class="price-sale">{{ number_format($product->sale_price, 2) }}</span>
                        </td>

                        {{-- المخزون --}}
                        <td>
                            @php
                                $qty = $product->quantity;
                                $stockClass = match($product->stock_status_color ?? '') {
                                    'green','success'   => 'stock-green',
                                    'yellow','warning'  => 'stock-yellow',
                                    'red','danger'      => 'stock-red',
                                    default             => 'stock-gray',
                                };
                                $stockIcon = match($product->stock_status_color ?? '') {
                                    'green','success'   => 'fa-check',
                                    'yellow','warning'  => 'fa-exclamation',
                                    'red','danger'      => 'fa-xmark',
                                    default             => 'fa-minus',
                                };
                            @endphp
                            <span class="stock-pill {{ $stockClass }}">
                                <i class="fas {{ $stockIcon }} text-xs"></i>
                                {{ number_format($qty) }} {{ $product->unit }}
                            </span>
                        </td>

                        {{-- الحالة --}}
                        <td>
                            @if($product->is_active)
                                <span class="status-active">
                                    <span class="status-dot dot-green"></span>
                                    نشط
                                </span>
                            @else
                                <span class="status-inactive">
                                    <span class="status-dot dot-red"></span>
                                    موقوف
                                </span>
                            @endif
                        </td>

                        {{-- الإجراءات --}}
                        <td>
                            <div class="flex items-center justify-center gap-1">

                                <a href="{{ route('products.show', $product) }}"
                                   class="action-btn view" title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if(auth()->user()->hasPermission('products.edit'))
                                <a href="{{ route('products.edit', $product) }}"
                                   class="action-btn edit" title="تعديل">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                @endif

                                @if(auth()->user()->hasPermission('products.delete'))
                                <form method="POST" action="{{ route('products.destroy', $product) }}"
                                      onsubmit="return confirmDelete(this)">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn del" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif

                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <p class="font-medium text-gray-500">لا توجد منتجات مطابقة</p>
                                <p class="text-sm mt-1">جرّب تغيير معايير البحث أو
                                    <a href="{{ route('products.index') }}" class="text-[#146E6E] hover:underline">استعرض كل المنتجات</a>
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
        <div class="pagination-wrap">
            {{ $products->appends(request()->query())->links() }}
        </div>
        @endif

    </div>

</div>
@endsection

@push('scripts')
<script>
// ── تعريف المتغيرات من Blade مرة واحدة بعيداً عن template literals ──
var SEARCH_URL     = window.location.origin + '/products/search';
var NO_PRODUCT_IMG = '{{ asset("images/no-product.png") }}';

function confirmDelete(form) {
    var nameEl = form.closest('tr').querySelector('.product-name-ar');
    var name   = nameEl ? nameEl.textContent.trim() : 'هذا المنتج';
    return confirm('هل أنت متأكد من حذف "' + name + '"؟\nلا يمكن التراجع عن هذا الإجراء.');
}

// ──── Live Search (البحث الحي) ────
var searchInput        = document.getElementById('live-search');
var searchResults      = document.getElementById('search-results');
var searchResultsPanel = document.getElementById('search-results-panel');
var searchTimeout      = null;

function positionSearchPanel() {
    var filterBar = document.querySelector('.filter-bar');
    if (!filterBar || !searchResults) return;
    var rect = filterBar.getBoundingClientRect();
    var panel = searchResultsPanel;
    panel.style.top    = (rect.bottom + window.scrollY + 8) + 'px';
    panel.style.left   = rect.left + 'px';
    panel.style.width  = rect.width + 'px';
    // reposition to fixed coords
    panel.style.position = 'fixed';
    panel.style.top    = (rect.bottom + 8) + 'px';
    panel.style.left   = rect.left + 'px';
}

if (searchInput) {

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            // اضغط Enter لإرسال الفورم والبحث الكامل في الجدول
            searchResults.classList.add('hidden');
            searchResultsPanel.innerHTML = '';
            searchInput.closest('form').submit();
        }
        if (e.key === 'Escape') {
            searchResults.classList.add('hidden');
            searchResultsPanel.innerHTML = '';
        }
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        var query = this.value.trim();

        if (query.length === 0) {
            searchResults.classList.add('hidden');
            searchResultsPanel.innerHTML = '';
            return;
        }

        if (query.length < 2) {
            searchResults.classList.remove('hidden');
            searchResults.innerHTML =
                '<div class="p-3 text-center text-xs text-gray-400">اكتب حرفين على الأقل للبحث...</div>';
            return;
        }

        showLoadingState();
        searchResults.classList.remove('hidden');

        searchTimeout = setTimeout(function() {
            performSearch(query);
        }, 350);
    });

    window.addEventListener('scroll', function() {
    if (!searchResults.classList.contains('hidden')) positionSearchPanel();
}, { passive: true });
window.addEventListener('resize', function() {
    if (!searchResults.classList.contains('hidden')) positionSearchPanel();
});

document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
            searchResultsPanel.innerHTML = '';
        }
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2 && searchResults.innerHTML !== '') {
            searchResults.classList.remove('hidden');
        }
    });
}

function performSearch(query) {
    var url = SEARCH_URL + '?q=' + encodeURIComponent(query);

    fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(function(response) {
        if (response.status === 401) { window.location.reload(); return null; }
        if (response.status === 403) { showErrorState('ليس لديك صلاحية لهذا الإجراء.'); return null; }
        if (!response.ok)            { showErrorState('خطأ في الخادم (' + response.status + '). حاول مجدداً.'); return null; }

        var ct = response.headers.get('content-type') || '';
        if (ct.indexOf('application/json') === -1) {
            window.location.reload();
            return null;
        }
        return response.json();
    })
    .then(function(data) {
        if (!data) return;
        displaySearchResults(data.results || []);
    })
    .catch(function(err) {
        console.error('Live search error:', err);
        showErrorState('تعذّر الاتصال بالخادم. تحقق من الاتصال وحاول مجدداً.');
    });
}

function showLoadingState() {
    positionSearchPanel();
    searchResults.classList.remove('hidden');
    searchResultsPanel.innerHTML =
        '<div class="p-4 text-center">' +
            '<div class="inline-flex items-center gap-2 text-[#146E6E]">' +
                '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">' +
                    '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>' +
                    '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>' +
                '</svg>' +
                '<span class="text-sm">جاري البحث...</span>' +
            '</div>' +
        '</div>';
}

function showErrorState(msg) {
    var message = msg || 'حدث خطأ في البحث. حاول مجدداً.';
    positionSearchPanel();
    searchResultsPanel.innerHTML =
        '<div class="p-4 text-center text-red-500">' +
            '<i class="fas fa-exclamation-circle text-2xl mb-2 block opacity-60"></i>' +
            '<p class="text-sm">' + message + '</p>' +
        '</div>';
    searchResults.classList.remove('hidden');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function displaySearchResults(results) {
    if (results.length === 0) {
        positionSearchPanel();
        searchResultsPanel.innerHTML =
            '<div class="p-4 text-center text-gray-500">' +
                '<i class="fas fa-search text-2xl mb-2 block opacity-40"></i>' +
                '<p class="text-sm">لا توجد نتائج مطابقة</p>' +
            '</div>';
        searchResults.classList.remove('hidden');
        return;
    }

    var html =
        '<div style="position:sticky;top:0;z-index:1;background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border-bottom:2px solid #0d9488;border-radius:16px 16px 0 0;padding:10px 16px;display:flex;align-items:center;justify-content:space-between;">' +
            '<span style="font-size:.8rem;font-weight:700;color:#0f766e;">' +
                '<i class="fas fa-search" style="margin-left:6px;opacity:.7;"></i>' +
                'وجدنا <span style="font-size:1.1rem;">' + results.length + '</span> منتج مطابق' +
            '</span>' +
            '<span style="font-size:.75rem;color:#6b7280;">اضغط على المنتج للتفاصيل</span>' +
        '</div>';

    results.forEach(function(product) {
        var imgSrc   = product.image ? escapeHtml(product.image) : NO_PRODUCT_IMG;
        var nameAr   = escapeHtml(product.name_ar);
        var nameEn   = escapeHtml(product.name_en);
        var sku      = escapeHtml(product.sku);
        var category = escapeHtml(product.category) || 'بدون فئة';
        var price    = parseFloat(product.sale_price || 0).toLocaleString('ar-SA');
        var qty      = parseInt(product.quantity, 10) || 0;
        var qtyClass = qty > 0 ? 'text-green-600' : 'text-red-600';

        html +=
            '<a href="/products/' + product.id + '" ' +
               'style="display:flex;align-items:center;gap:16px;padding:14px 20px;border-bottom:1px solid #f3f4f6;text-decoration:none;transition:background .15s;"' +
               'onmouseover="this.style.background=\'#f0fdfa\'" onmouseout="this.style.background=\'transparent\'">' +

                '<img src="' + imgSrc + '" ' +
                     'alt="' + nameAr + '" ' +
                     'style="width:56px;height:56px;border-radius:12px;object-fit:cover;border:2px solid #e5e7eb;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.08);" ' +
                     'onerror="this.onerror=null;this.src=\'' + NO_PRODUCT_IMG + '\'">' +

                '<div style="flex:1;min-width:0;">' +
                    '<div style="font-weight:700;color:#111827;font-size:.95rem;margin-bottom:3px;">' + nameAr + '</div>' +
                    (nameEn ? '<div style="font-size:.8rem;color:#9ca3af;margin-bottom:4px;">' + nameEn + '</div>' : '') +
                    '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">' +
                        '<span style="background:linear-gradient(135deg,#f0fdfa,#ccfbf1);color:#0d9488;border:2px solid #99f6e4;border-radius:8px;padding:2px 10px;font-size:.78rem;font-weight:700;font-family:monospace;">' + sku + '</span>' +
                        '<span style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);color:#166534;border:1px solid #86efac;border-radius:8px;padding:2px 8px;font-size:.78rem;font-weight:600;">' + category + '</span>' +
                    '</div>' +
                '</div>' +

                '<div style="flex-shrink:0;text-align:center;">' +
                    '<div style="font-size:1.1rem;font-weight:800;color:#0d9488;background:linear-gradient(135deg,#f0fdfa,#ccfbf1);padding:4px 12px;border-radius:10px;border:1px solid #99f6e4;white-space:nowrap;">' + price + ' ر.س</div>' +
                    '<div style="font-size:.78rem;font-weight:700;margin-top:4px;color:' + (qty > 0 ? '#16a34a' : '#dc2626') + ';">' +
                        (qty > 0 ? '✓ ' : '✗ ') + qty + ' ' + escapeHtml(product.unit || 'قطعة') +
                    '</div>' +
                '</div>' +

                '<div style="flex-shrink:0;color:#9ca3af;font-size:1rem;"><i class="fas fa-chevron-left"></i></div>' +

            '</a>';
    });

    html += '<div style="padding:10px 20px;background:#f9fafb;border-top:1px solid #f3f4f6;border-radius:0 0 16px 16px;display:flex;align-items:center;justify-content:center;gap:8px;">' +
        '<span style="font-size:.8rem;color:#6b7280;">اضغط</span>' +
        '<kbd style="background:#fff;border:1px solid #d1d5db;border-radius:6px;padding:2px 8px;font-size:.78rem;color:#374151;box-shadow:0 1px 3px rgba(0,0,0,.1);">Enter</kbd>' +
        '<span style="font-size:.8rem;color:#6b7280;">لعرض كل النتائج في الجدول</span>' +
    '</div>';
    positionSearchPanel();
    searchResultsPanel.innerHTML = html;
    searchResults.classList.remove('hidden');
}
</script>
@endpush
