{{-- المسار الكامل: resources/views/products/index.blade.php --}}

@extends('layouts.app')

@section('title', 'المنتجات')

@push('styles')
<style>
    .products-hero {
        background: linear-gradient(135deg, #146E6E 0%, #0D5050 100%);
        border-radius: 16px;
        padding: 1.75rem 2rem;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .products-hero::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .products-hero::after {
        content: '';
        position: absolute;
        bottom: -60px; left: -20px;
        width: 160px; height: 160px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }

    /* ── بطاقات الإحصائيات ── */
    .stat-tile {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: box-shadow .2s, transform .2s, border-color .2s;
        text-decoration: none;
        cursor: pointer;
    }
    .stat-tile:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .stat-tile.active { border-color: #146E6E; box-shadow: 0 0 0 3px rgba(20,110,110,0.12); }
    .stat-tile-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .stat-tile-value { font-size: 1.75rem; font-weight: 700; line-height: 1; }
    .stat-tile-label { font-size: .8rem; color: #6b7280; margin-top: 3px; }

    /* ── شريط الفلاتر ── */
    .filter-bar {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1rem 1.25rem;
    }
    .filter-input {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: .55rem .9rem;
        font-size: .875rem;
        width: 100%;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        background: #f9fafb;
        font-family: 'Tajawal', sans-serif;
    }
    .filter-input:focus { border-color: #146E6E; box-shadow: 0 0 0 3px rgba(20,110,110,0.1); background:#fff; }

    /* ── الجدول ── */
    .products-table-wrap {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }
    .products-table {
        width: 100%;
        border-collapse: collapse;
    }
    .products-table thead tr {
        background: #f8fafc;
        border-bottom: 2px solid #e5e7eb;
    }
    .products-table thead th {
        padding: .85rem 1rem;
        font-size: .78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        text-align: right;
        white-space: nowrap;
    }
    .products-table thead th:first-child { padding-right: 1.25rem; }
    .products-table thead th:last-child  { padding-left: 1.25rem;  }

    .products-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background .15s;
    }
    .products-table tbody tr:last-child { border-bottom: none; }
    .products-table tbody tr:hover { background: #f0fdfc; }

    .products-table tbody td {
        padding: .9rem 1rem;
        font-size: .875rem;
        color: #374151;
        vertical-align: middle;
        text-align: right;
    }
    .products-table tbody td:first-child { padding-right: 1.25rem; }
    .products-table tbody td:last-child  { padding-left: 1.25rem;  }

    /* ── صورة + اسم المنتج ── */
    .product-name-cell { display: flex; align-items: center; gap: .75rem; }
    .product-thumb {
        width: 42px; height: 42px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        flex-shrink: 0;
        background: #f9fafb;
    }
    .product-name-ar { font-weight: 600; color: #111827; font-size: .9rem; line-height: 1.3; }
    .product-name-en { font-size: .75rem; color: #9ca3af; margin-top: 1px; }

    /* ── SKU badge ── */
    .sku-badge {
        display: inline-block;
        background: #f0fdfa;
        color: #146E6E;
        border: 1px solid #97c9c8;
        border-radius: 7px;
        padding: .2rem .6rem;
        font-size: .78rem;
        font-weight: 600;
        font-family: 'Courier New', monospace;
        letter-spacing: .03em;
        white-space: nowrap;
    }

    /* ── سعر ── */
    .price-purchase { color: #6b7280; font-size: .875rem; }
    .price-sale { color: #146E6E; font-weight: 700; font-size: .95rem; }

    /* ── المخزون ── */
    .stock-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .25rem .65rem;
        border-radius: 8px;
        font-size: .8rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .stock-green  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .stock-yellow { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }
    .stock-red    { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
    .stock-gray   { background: #f9fafb; color: #6b7280; border: 1px solid #e5e7eb; }

    /* ── الحالة ── */
    .status-active   { display:inline-flex; align-items:center; gap:.3rem; background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; border-radius:20px; padding:.2rem .7rem; font-size:.78rem; font-weight:600; }
    .status-inactive { display:inline-flex; align-items:center; gap:.3rem; background:#fff1f2; color:#9f1239; border:1px solid #fecdd3; border-radius:20px; padding:.2rem .7rem; font-size:.78rem; font-weight:600; }
    .status-dot { width:6px; height:6px; border-radius:50%; }
    .dot-green  { background: #10b981; }
    .dot-red    { background: #f43f5e; }

    /* ── أزرار الإجراءات ── */
    .action-btn {
        width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: .9rem;
        transition: background .15s, border-color .15s, color .15s;
        color: #9ca3af;
        cursor: pointer;
        background: none;
    }
    .action-btn:hover { background: #f3f4f6; border-color: #e5e7eb; color: #374151; }
    .action-btn.view:hover  { background: #f0fdfc; border-color: #97c9c8; color: #146E6E; }
    .action-btn.edit:hover  { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
    .action-btn.del:hover   { background: #fff1f2; border-color: #fecdd3; color: #e11d48; }

    /* ── فارغ ── */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #9ca3af;
    }
    .empty-state i { font-size: 3.5rem; display: block; margin-bottom: 1rem; opacity: .4; }
    .empty-state p { font-size: .95rem; }

    /* ── pagination ── */
    .pagination-wrap {
        padding: 1rem 1.5rem;
        border-top: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: between;
    }

    /* ── تسليط الضوء على صف محدد ── */
    @keyframes rowHighlight {
        from { background: #ccfbf1; }
        to   { background: transparent; }
    }
    .row-flash { animation: rowHighlight 1.2s ease-out; }
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
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="اسم المنتج، الكود، الباركود..."
                           class="filter-input pr-8">
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
function confirmDelete(form) {
    const name = form.closest('tr').querySelector('.product-name-ar')?.textContent?.trim() ?? 'هذا المنتج';
    return confirm(`هل أنت متأكد من حذف "${name}"؟\nلا يمكن التراجع عن هذا الإجراء.`);
}
</script>
@endpush