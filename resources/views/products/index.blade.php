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
        position: relative; /* مهم لحساب موضع البانل */
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

    /* ── Infinite Scroll sentinel & loader ── */
    #infinite-scroll-sentinel {
        height: 1px;
    }
    #infinite-scroll-loader {
        padding: 1.5rem 2rem;
        border-top: 2px solid #f3f4f6;
        display: none;
        align-items: center;
        justify-content: center;
        gap: .75rem;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        color: #0d9488;
        font-size: .9rem;
        font-weight: 600;
    }
    #infinite-scroll-loader.active { display: flex; }
    #infinite-scroll-end {
        padding: 1.25rem 2rem;
        border-top: 2px solid #f3f4f6;
        display: none;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        color: #9ca3af;
        font-size: .82rem;
    }
    #infinite-scroll-end.visible { display: flex; }

    /* ── البانل العائم للبحث الحي ── */
    #search-results-panel {
        position: fixed;
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 4px 16px rgba(13,148,136,0.1);
        max-height: 480px;
        overflow-y: auto;
        z-index: 9999;
        display: none; /* مخفي افتراضياً */
    }

    /* ── تسليط الضوء على صف محدد ── */
    @keyframes rowHighlight {
        from { background: linear-gradient(135deg, #ccfbf1 0%, #d1fae5 100%); }
        to   { background: transparent; }
    }
    .row-flash { animation: rowHighlight 1.5s ease-out; }

    /* ── زر مسح الباركود ── */
    .barcode-scan-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: linear-gradient(135deg, #0d9488, #0f766e);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 0 14px;
        height: 42px;
        font-size: .85rem;
        font-weight: 700;
        font-family: 'Tajawal', sans-serif;
        cursor: pointer;
        transition: all .2s;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(13,148,136,.3);
        flex-shrink: 0;
    }
    .barcode-scan-btn:hover {
        background: linear-gradient(135deg, #0f766e, #115e59);
        box-shadow: 0 6px 20px rgba(13,148,136,.4);
        transform: translateY(-1px);
    }
    .barcode-scan-btn:active { transform: translateY(0); }
    .barcode-scan-btn .scan-icon { font-size: 1rem; }

    /* ── مودال الكاميرا ── */
    #barcode-modal {
        position: fixed; inset: 0; z-index: 99999;
        display: none; align-items: center; justify-content: center;
    }
    #barcode-modal.active { display: flex; }
    #barcode-modal-backdrop {
        position: absolute; inset: 0;
        background: rgba(0,0,0,.75);
        backdrop-filter: blur(6px);
    }
    #barcode-modal-box {
        position: relative; z-index: 1;
        background: #fff;
        border-radius: 24px;
        padding: 0;
        width: min(460px, calc(100vw - 32px));
        box-shadow: 0 30px 80px rgba(0,0,0,.4);
        overflow: hidden;
        animation: modalSlideIn .25s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes modalSlideIn {
        from { opacity:0; transform: scale(.85) translateY(20px); }
        to   { opacity:1; transform: scale(1)  translateY(0);     }
    }
    #barcode-modal-header {
        background: linear-gradient(135deg, #0d9488, #0f766e);
        padding: 1.25rem 1.5rem;
        display: flex; align-items: center; justify-content: space-between;
        color: #fff;
    }
    #barcode-modal-header h3 {
        font-size: 1rem; font-weight: 700; margin: 0;
        display: flex; align-items: center; gap: 10px;
    }
    #barcode-modal-close {
        background: rgba(255,255,255,.2); border: none; color: #fff;
        width: 32px; height: 32px; border-radius: 50%; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem; transition: background .2s;
    }
    #barcode-modal-close:hover { background: rgba(255,255,255,.35); }

    #barcode-modal-body { padding: 1.25rem; }

    /* منطقة الفيديو */
    #barcode-video-wrap {
        position: relative;
        background: #000;
        border-radius: 16px;
        overflow: hidden;
        aspect-ratio: 4/3;
        width: 100%;
    }
    #barcode-video {
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
    }
    /* خط المسح المتحرك */
    #scan-line {
        position: absolute; left: 10%; right: 10%; height: 2px;
        background: linear-gradient(90deg, transparent, #0d9488, #34d399, #0d9488, transparent);
        box-shadow: 0 0 8px rgba(13,148,136,.8);
        animation: scanMove 2s ease-in-out infinite;
        border-radius: 2px;
    }
    @keyframes scanMove {
        0%   { top: 15%; opacity: 1; }
        50%  { top: 80%; opacity: 1; }
        100% { top: 15%; opacity: 1; }
    }
    /* إطار التصويب */
    #scan-frame {
        position: absolute; inset: 10%;
        border: 2px solid rgba(13,148,136,.6);
        border-radius: 12px;
        box-shadow: 0 0 0 2000px rgba(0,0,0,.35);
    }
    #scan-frame::before,
    #scan-frame::after,
    #scan-frame .corner-br,
    #scan-frame .corner-bl {
        content: '';
        position: absolute;
        width: 22px; height: 22px;
        border-color: #0d9488; border-style: solid;
    }
    #scan-frame::before  { top:-2px; right:-2px; border-width:3px 3px 0 0; border-radius:0 6px 0 0; }
    #scan-frame::after   { top:-2px; left:-2px;  border-width:3px 0 0 3px; border-radius:6px 0 0 0; }
    #scan-frame .corner-br { bottom:-2px; right:-2px; border-width:0 3px 3px 0; border-radius:0 0 6px 0; }
    #scan-frame .corner-bl { bottom:-2px; left:-2px;  border-width:0 0 3px 3px; border-radius:0 0 0 6px; }

    /* حالة النجاح */
    #barcode-video-wrap.success-flash {
        animation: successFlash .5s ease;
    }
    @keyframes successFlash {
        0%   { box-shadow: none; }
        50%  { box-shadow: 0 0 0 6px rgba(13,148,136,.5); }
        100% { box-shadow: none; }
    }

    /* حقل الباركود اليدوي */
    #manual-barcode-input {
        width: 100%;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: .65rem 1rem;
        font-size: .9rem;
        font-family: 'Tajawal', sans-serif;
        outline: none;
        transition: border-color .2s;
        direction: ltr;
        text-align: center;
        letter-spacing: .05em;
    }
    #manual-barcode-input:focus { border-color: #0d9488; box-shadow: 0 0 0 4px rgba(13,148,136,.1); }

    /* شريط الحالة */
    #scan-status {
        text-align: center;
        font-size: .82rem;
        padding: .5rem 0 .25rem;
        color: #6b7280;
        min-height: 24px;
        transition: color .3s;
    }
    #scan-status.scanning { color: #0d9488; }
    #scan-status.found    { color: #16a34a; font-weight: 700; }
    #scan-status.error    { color: #dc2626; }

    /* اختيار الكاميرا */
    #camera-select {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: .5rem .75rem;
        font-size: .82rem;
        font-family: 'Tajawal', sans-serif;
        background: #f9fafb;
        color: #374151;
        outline: none;
    }
    #camera-select:focus { border-color: #0d9488; }
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
    <div class="filter-bar" id="filter-bar">
        <form method="GET" action="{{ route('products.index') }}"
              class="flex flex-wrap md:flex-nowrap items-end gap-3">

            {{-- البحث --}}
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-400 mb-1.5 font-medium">بحث</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <i class="fas fa-search absolute top-1/2 -translate-y-1/2 right-3 text-gray-400 text-xs pointer-events-none"></i>
                        <input type="text" name="search" id="live-search" value="{{ request('search') }}"
                               placeholder="اسم المنتج، الكود، الباركود..."
                               class="filter-input pr-8"
                               autocomplete="off">
                    </div>
                    {{-- زر مسح الباركود بالكاميرا --}}
                    <button type="button" id="open-barcode-scanner" class="barcode-scan-btn" title="مسح الباركود بالكاميرا">
                        <i class="fas fa-camera scan-icon"></i>
                        <span class="hidden sm:inline">باركود</span>
                    </button>
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

    {{-- ─── مودال ماسح الباركود بالكاميرا ───────────────────────────────── --}}
    <div id="barcode-modal" role="dialog" aria-modal="true" aria-label="ماسح الباركود">
        <div id="barcode-modal-backdrop"></div>
        <div id="barcode-modal-box">

            {{-- رأس المودال --}}
            <div id="barcode-modal-header">
                <h3>
                    <i class="fas fa-barcode"></i>
                    مسح الباركود بالكاميرا
                </h3>
                <button id="barcode-modal-close" title="إغلاق" aria-label="إغلاق">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- جسم المودال --}}
            <div id="barcode-modal-body">

                {{-- اختيار الكاميرا --}}
                <div class="mb-3" id="camera-select-wrap" style="display:none;">
                    <label class="block text-xs text-gray-500 mb-1.5 font-medium">
                        <i class="fas fa-video ml-1 text-teal-600"></i>
                        اختيار الكاميرا
                    </label>
                    <select id="camera-select"></select>
                </div>

                {{-- منطقة الفيديو --}}
                <div id="barcode-video-wrap">
                    <video id="barcode-video" playsinline muted></video>
                    <canvas id="barcode-canvas" style="display:none;"></canvas>
                    {{-- إطار التصويب --}}
                    <div id="scan-frame">
                        <div class="corner-br"></div>
                        <div class="corner-bl"></div>
                    </div>
                    {{-- خط المسح --}}
                    <div id="scan-line"></div>
                    {{-- طبقة التحميل --}}
                    <div id="camera-loading" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.6);color:#fff;flex-direction:column;gap:12px;font-size:.9rem;">
                        <i class="fas fa-circle-notch fa-spin" style="font-size:2rem;color:#0d9488;"></i>
                        <span>جاري تشغيل الكاميرا...</span>
                    </div>
                </div>

                {{-- حالة المسح --}}
                <div id="scan-status" class="scanning">
                    <i class="fas fa-circle-notch fa-spin" style="margin-left:5px;"></i>
                    وجّه الكاميرا نحو الباركود
                </div>

                {{-- فاصل --}}
                <div style="display:flex;align-items:center;gap:10px;margin:.75rem 0;">
                    <div style="flex:1;height:1px;background:#e5e7eb;"></div>
                    <span style="font-size:.78rem;color:#9ca3af;white-space:nowrap;">أو أدخل يدوياً</span>
                    <div style="flex:1;height:1px;background:#e5e7eb;"></div>
                </div>

                {{-- إدخال يدوي --}}
                <div style="display:flex;gap:8px;">
                    <input type="text" id="manual-barcode-input"
                           placeholder="اكتب الباركود هنا..."
                           autocomplete="off" inputmode="text">
                    <button type="button" id="manual-barcode-btn"
                            style="background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border:none;border-radius:12px;padding:0 16px;font-size:.85rem;font-weight:700;font-family:'Tajawal',sans-serif;cursor:pointer;white-space:nowrap;transition:opacity .2s;"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        بحث
                    </button>
                </div>

            </div>{{-- /body --}}
        </div>{{-- /box --}}
    </div>{{-- /modal --}}

    {{-- ─── بانل نتائج البحث الحي (خارج أي container) ─────────────────── --}}
    <div id="search-results-panel"></div>

    {{-- ─── جدول المنتجات ─────────────────────────────────────────────── --}}
    <div class="products-table-wrap">

        {{-- رأس الجدول: عدد النتائج --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-white">
            <span class="text-xs text-gray-400 font-medium">
                عرض <span id="showing-count">{{ $products->count() }}</span> من
                <span id="total-count">{{ $products->total() }}</span> منتج
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
                <tbody id="products-tbody"
                       data-next-page="{{ $products->hasMorePages() ? 2 : '' }}"
                       data-has-more="{{ $products->hasMorePages() ? 'true' : 'false' }}"
                       data-load-url="{{ route('products.index', request()->query()) }}">
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
                    <tr id="infinite-scroll-sentinel" style="height:1px;border:none;background:transparent;"><td colspan="8" style="padding:0;border:none;"></td></tr>
                </tbody>
            </table>
        </div>

        <div id="infinite-scroll-loader">
            <svg class="spin" style="width:20px;height:20px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            جاري تحميل المزيد من المنتجات...
        </div>

        <div id="infinite-scroll-end">
            <i class="fas fa-check-circle" style="color:#0d9488;font-size:1rem;"></i>
            تم عرض جميع المنتجات
            <span id="scroll-total-badge"
                  style="background:linear-gradient(135deg,#f0fdfa,#ccfbf1);color:#0f766e;border:1px solid #99f6e4;
                         border-radius:20px;padding:2px 10px;font-size:.78rem;font-weight:700;margin-right:4px;">
                {{ $products->total() }}
            </span>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
// ── المتغيرات العامة ──
var SEARCH_URL     = window.location.origin + '/products/search';
var NO_PRODUCT_IMG = '{{ asset("images/no-product.png") }}';

function confirmDelete(form) {
    var nameEl = form.closest('tr').querySelector('.product-name-ar');
    var name   = nameEl ? nameEl.textContent.trim() : 'هذا المنتج';
    return confirm('هل أنت متأكد من حذف "' + name + '"؟\nلا يمكن التراجع عن هذا الإجراء.');
}

// ──────────────────────────────────────────────────────────────────────────
// ── Infinite Scroll (Lazy Loading) ────────────────────────────────────────
// ──────────────────────────────────────────────────────────────────────────
(function () {
    var tbody      = document.getElementById('products-tbody');
    var sentinel   = document.getElementById('infinite-scroll-sentinel');
    var loader     = document.getElementById('infinite-scroll-loader');
    var endBanner  = document.getElementById('infinite-scroll-end');
    var showingEl  = document.getElementById('showing-count');

    if (!tbody || !sentinel) return;

    var isLoading = false;
    var hasMore   = tbody.dataset.hasMore === 'true';
    var nextPage  = parseInt(tbody.dataset.nextPage, 10) || null;
    var baseUrl   = tbody.dataset.loadUrl || window.location.href;

    // إذا لا توجد صفحات إضافية من البداية، أظهر نهاية القائمة فوراً
    if (!hasMore) {
        endBanner.classList.add('visible');
    }

    function buildPageUrl(page) {
        var url    = new URL(baseUrl, window.location.origin);
        var params = new URLSearchParams(url.search);
        params.set('page', page);
        url.search = params.toString();
        return url.toString();
    }

    function loadNextPage() {
        if (isLoading || !hasMore || !nextPage) return;

        isLoading = true;
        loader.classList.add('active');

        fetch(buildPageUrl(nextPage), {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':     (document.querySelector('meta[name="csrf-token"]') || {}).getAttribute('content') || ''
            }
        })
        .then(function (response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        })
        .then(function (data) {
            // أضف الصفوف الجديدة إلى الـ tbody
            var temp = document.createElement('tbody');
            temp.innerHTML = data.html;
            while (temp.firstChild) {
                sentinel.parentNode.insertBefore(temp.firstChild, sentinel);
            }

            // حدّث العداد
            if (showingEl && data.showing) {
                showingEl.textContent = data.showing;
            }

            hasMore  = data.hasMore;
            nextPage = data.hasMore ? data.nextPage : null;

            loader.classList.remove('active');
            isLoading = false;

            if (!hasMore) {
                endBanner.classList.add('visible');
                // لم نعد نحتاج الـ observer
                if (observer) observer.disconnect();
            }
        })
        .catch(function (err) {
            console.error('Infinite scroll error:', err);
            loader.classList.remove('active');
            isLoading = false;
        });
    }

    // ── IntersectionObserver: يراقب الـ sentinel في نهاية الجدول ──────────
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting && !isLoading && hasMore) {
                loadNextPage();
            }
        });
    }, {
        root:       null,         // viewport
        rootMargin: '0px 0px 200px 0px',  // ابدأ التحميل 200px قبل الوصول
        threshold:  0
    });

    observer.observe(sentinel);
})();

// ──────────────────────────────────────────────────────────────────────────
// ── Live Search (البحث الحي) ──────────────────────────────────────────────
// ──────────────────────────────────────────────────────────────────────────
var searchInput   = document.getElementById('live-search');
var searchPanel   = document.getElementById('search-results-panel');
var searchTimeout = null;

function positionSearchPanel() {
    var filterBar = document.getElementById('filter-bar');
    if (!filterBar || !searchPanel) return;

    var rect = filterBar.getBoundingClientRect();

    searchPanel.style.top   = (rect.bottom + 8) + 'px';
    searchPanel.style.left  = rect.left + 'px';
    searchPanel.style.width = rect.width + 'px';
}

function showPanel() {
    positionSearchPanel();
    searchPanel.style.display = 'block';
}

function hidePanel() {
    searchPanel.style.display = 'none';
    searchPanel.innerHTML = '';
}

if (searchInput) {

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            hidePanel();
            searchInput.closest('form').submit();
        }
        if (e.key === 'Escape') {
            hidePanel();
        }
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        var query = this.value.trim();

        if (query.length === 0) {
            hidePanel();
            return;
        }

        if (query.length < 2) {
            searchPanel.innerHTML =
                '<div style="padding:12px 16px;text-align:center;font-size:.85rem;color:#9ca3af;">اكتب حرفين على الأقل للبحث...</div>';
            showPanel();
            return;
        }

        showLoadingState();

        searchTimeout = setTimeout(function() {
            performSearch(query);
        }, 350);
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2 && searchPanel.innerHTML !== '') {
            showPanel();
        }
    });

    window.addEventListener('scroll', function() {
        if (searchPanel.style.display !== 'none') positionSearchPanel();
    }, { passive: true });

    window.addEventListener('resize', function() {
        if (searchPanel.style.display !== 'none') positionSearchPanel();
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchPanel.contains(e.target)) {
            hidePanel();
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
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).getAttribute('content') || ''
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
    searchPanel.innerHTML =
        '<div style="padding:16px;text-align:center;">' +
            '<div style="display:inline-flex;align-items:center;gap:8px;color:#146E6E;">' +
                '<svg style="animation:spin 1s linear infinite;width:18px;height:18px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">' +
                    '<circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>' +
                    '<path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>' +
                '</svg>' +
                '<span style="font-size:.9rem;">جاري البحث...</span>' +
            '</div>' +
        '</div>';
    showPanel();
}

function showErrorState(msg) {
    searchPanel.innerHTML =
        '<div style="padding:16px;text-align:center;color:#e11d48;">' +
            '<i class="fas fa-exclamation-circle" style="font-size:1.5rem;display:block;margin-bottom:8px;opacity:.6;"></i>' +
            '<p style="font-size:.85rem;">' + (msg || 'حدث خطأ في البحث. حاول مجدداً.') + '</p>' +
        '</div>';
    showPanel();
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
        searchPanel.innerHTML =
            '<div style="padding:24px;text-align:center;color:#6b7280;">' +
                '<i class="fas fa-search" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.35;"></i>' +
                '<p style="font-size:.9rem;">لا توجد نتائج مطابقة</p>' +
            '</div>';
        showPanel();
        return;
    }

    var html =
        '<div style="position:sticky;top:0;z-index:1;background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border-bottom:2px solid #0d9488;border-radius:16px 16px 0 0;padding:10px 16px;display:flex;align-items:center;justify-content:space-between;">' +
            '<span style="font-size:.8rem;font-weight:700;color:#0f766e;">' +
                '<i class="fas fa-search" style="margin-left:6px;opacity:.7;"></i>' +
                'وجدنا <span style="font-size:1.05rem;">' + results.length + '</span> منتج مطابق' +
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
        var unit     = escapeHtml(product.unit || 'قطعة');

        html +=
            '<a href="/products/' + product.id + '" ' +
               'style="display:flex;align-items:center;gap:16px;padding:14px 20px;border-bottom:1px solid #f3f4f6;text-decoration:none;transition:background .15s;" ' +
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
                    '<div style="font-size:1.05rem;font-weight:800;color:#0d9488;background:linear-gradient(135deg,#f0fdfa,#ccfbf1);padding:4px 12px;border-radius:10px;border:1px solid #99f6e4;white-space:nowrap;">' + price + ' ر.س</div>' +
                    '<div style="font-size:.78rem;font-weight:700;margin-top:4px;color:' + (qty > 0 ? '#16a34a' : '#dc2626') + ';">' +
                        (qty > 0 ? '✓ ' : '✗ ') + qty + ' ' + unit +
                    '</div>' +
                '</div>' +

                '<div style="flex-shrink:0;color:#9ca3af;font-size:1rem;"><i class="fas fa-chevron-left"></i></div>' +

            '</a>';
    });

    html +=
        '<div style="padding:10px 20px;background:#f9fafb;border-top:1px solid #f3f4f6;border-radius:0 0 16px 16px;display:flex;align-items:center;justify-content:center;gap:8px;">' +
            '<span style="font-size:.8rem;color:#6b7280;">اضغط</span>' +
            '<kbd style="background:#fff;border:1px solid #d1d5db;border-radius:6px;padding:2px 8px;font-size:.78rem;color:#374151;box-shadow:0 1px 3px rgba(0,0,0,.1);">Enter</kbd>' +
            '<span style="font-size:.8rem;color:#6b7280;">لعرض كل النتائج في الجدول</span>' +
        '</div>';

    searchPanel.innerHTML = html;
    showPanel();
}

// CSS للـ spin animation
var style = document.createElement('style');
style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);

// ──────────────────────────────────────────────────────────────────────────
// ── ماسح الباركود بالكاميرا (ZXing-js)
// ──────────────────────────────────────────────────────────────────────────
(function() {

    // تحميل مكتبة ZXing ديناميكياً
    var ZXING_CDN = 'https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js';

    var barcodeModal      = document.getElementById('barcode-modal');
    var barcodeModalClose = document.getElementById('barcode-modal-close');
    var barcodeBackdrop   = document.getElementById('barcode-modal-backdrop');
    var openScannerBtn    = document.getElementById('open-barcode-scanner');
    var scanStatus        = document.getElementById('scan-status');
    var cameraLoading     = document.getElementById('camera-loading');
    var videoEl           = document.getElementById('barcode-video');
    var cameraSelectWrap  = document.getElementById('camera-select-wrap');
    var cameraSelect      = document.getElementById('camera-select');
    var manualInput       = document.getElementById('manual-barcode-input');
    var manualBtn         = document.getElementById('manual-barcode-btn');
    var liveSearchInput   = document.getElementById('live-search');
    var videoWrap         = document.getElementById('barcode-video-wrap');

    if (!barcodeModal || !openScannerBtn) return;

    var codeReader   = null;
    var zxingLoaded  = false;
    var scanning     = false;
    var currentStream = null;

    // ── تحميل ZXing ──────────────────────────────────────────────────────
    function loadZXing(callback) {
        if (zxingLoaded) { callback(); return; }
        var s = document.createElement('script');
        s.src = ZXING_CDN;
        s.onload = function() { zxingLoaded = true; callback(); };
        s.onerror = function() {
            setScanStatus('error', '<i class="fas fa-exclamation-triangle ml-1"></i> تعذّر تحميل مكتبة المسح. تحقق من الاتصال.');
        };
        document.head.appendChild(s);
    }

    // ── فتح المودال ──────────────────────────────────────────────────────
    function openModal() {
        barcodeModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        setScanStatus('scanning', '<i class="fas fa-circle-notch fa-spin" style="margin-left:5px;"></i> جاري تشغيل الكاميرا...');
        cameraLoading.style.display = 'flex';

        loadZXing(function() {
            startScanner();
        });
    }

    // ── إغلاق المودال ────────────────────────────────────────────────────
    function closeModal() {
        stopScanner();
        barcodeModal.classList.remove('active');
        document.body.style.overflow = '';
        manualInput.value = '';
        setScanStatus('scanning', '<i class="fas fa-circle-notch fa-spin" style="margin-left:5px;"></i> وجّه الكاميرا نحو الباركود');
    }

    // ── تشغيل الماسح ─────────────────────────────────────────────────────
    function startScanner() {
        if (!window.ZXing) {
            setScanStatus('error', '<i class="fas fa-exclamation-circle ml-1"></i> المكتبة غير متاحة.');
            return;
        }

        try {
            codeReader = new ZXing.BrowserMultiFormatReader();
            codeReader.getVideoInputDevices().then(function(devices) {
                cameraLoading.style.display = 'none';

                if (!devices || devices.length === 0) {
                    setScanStatus('error', '<i class="fas fa-video-slash ml-1"></i> لا توجد كاميرا متاحة على هذا الجهاز.');
                    return;
                }

                // تعبئة قائمة الكاميرات
                cameraSelect.innerHTML = '';
                devices.forEach(function(device, idx) {
                    var opt = document.createElement('option');
                    opt.value = device.deviceId;
                    opt.text  = device.label || ('كاميرا ' + (idx + 1));
                    cameraSelect.appendChild(opt);
                });

                // إخفاء قائمة الاختيار إذا كانت كاميرا واحدة فقط
                if (devices.length > 1) {
                    cameraSelectWrap.style.display = 'block';
                }

                // تفضيل الكاميرا الخلفية على الهاتف
                var preferredId = null;
                devices.forEach(function(d) {
                    var lbl = (d.label || '').toLowerCase();
                    if (lbl.indexOf('back') !== -1 || lbl.indexOf('rear') !== -1 || lbl.indexOf('خلفي') !== -1) {
                        preferredId = d.deviceId;
                    }
                });
                var selectedId = preferredId || devices[devices.length - 1].deviceId;
                cameraSelect.value = selectedId;

                startDecode(selectedId);

            }).catch(function(err) {
                cameraLoading.style.display = 'none';
                handleCameraError(err);
            });

        } catch(e) {
            cameraLoading.style.display = 'none';
            setScanStatus('error', '<i class="fas fa-exclamation-circle ml-1"></i> خطأ في تشغيل الماسح: ' + e.message);
        }
    }

    // ── بدء الفك (decode) ─────────────────────────────────────────────────
    function startDecode(deviceId) {
        scanning = true;
        setScanStatus('scanning', '<i class="fas fa-barcode ml-1"></i> جاري المسح... وجّه الكاميرا نحو الباركود');

        codeReader.decodeFromVideoDevice(deviceId, videoEl, function(result, err) {
            if (result && scanning) {
                onBarcodeFound(result.getText());
            }
            // تجاهل أخطاء NotFoundException (لا باركود في الإطار الحالي)
            if (err && !(err instanceof ZXing.NotFoundException)) {
                console.warn('ZXing err:', err);
            }
        });
    }

    // ── إيقاف الماسح ─────────────────────────────────────────────────────
    function stopScanner() {
        scanning = false;
        if (codeReader) {
            try { codeReader.reset(); } catch(e) {}
            codeReader = null;
        }
        // إيقاف stream الكاميرا يدوياً
        if (videoEl && videoEl.srcObject) {
            videoEl.srcObject.getTracks().forEach(function(t) { t.stop(); });
            videoEl.srcObject = null;
        }
        cameraSelectWrap.style.display = 'none';
    }

    // ── عند اكتشاف باركود ────────────────────────────────────────────────
    function onBarcodeFound(code) {
        scanning = false; // إيقاف مؤقت لمنع التكرار

        // تأثير النجاح البصري
        videoWrap.classList.add('success-flash');
        setTimeout(function() { videoWrap.classList.remove('success-flash'); }, 500);

        // صوت بسيط (نبضة)
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.value = 880;
            gain.gain.setValueAtTime(.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(.001, ctx.currentTime + .2);
            osc.start(ctx.currentTime); osc.stop(ctx.currentTime + .2);
        } catch(e) {}

        setScanStatus('found', '<i class="fas fa-check-circle ml-1"></i> تم المسح: <strong>' + escapeHtml(code) + '</strong>');

        // وضع الكود في خانة البحث وإغلاق المودال والبحث
        if (liveSearchInput) liveSearchInput.value = code;
        closeModal();

        // البحث الحي فوراً
        if (typeof performSearch === 'function') {
            performSearch(code);
        } else {
            // fallback: إرسال الفورم
            if (liveSearchInput) liveSearchInput.closest('form').submit();
        }
    }

    // ── معالجة أخطاء الكاميرا ────────────────────────────────────────────
    function handleCameraError(err) {
        var msg = '';
        if (err && (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')) {
            msg = '<i class="fas fa-lock ml-1"></i> تم رفض إذن الكاميرا. اسمح بالوصول من إعدادات المتصفح ثم أعد المحاولة.';
        } else if (err && (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError')) {
            msg = '<i class="fas fa-video-slash ml-1"></i> لا توجد كاميرا. يمكنك إدخال الباركود يدوياً أدناه.';
        } else if (err && err.name === 'NotReadableError') {
            msg = '<i class="fas fa-exclamation-triangle ml-1"></i> الكاميرا مستخدمة من تطبيق آخر. أغلقه وأعد المحاولة.';
        } else {
            msg = '<i class="fas fa-exclamation-circle ml-1"></i> خطأ في الكاميرا. يمكنك إدخال الباركود يدوياً.';
        }
        setScanStatus('error', msg);
    }

    // ── ضبط حالة الشريط ──────────────────────────────────────────────────
    function setScanStatus(type, html) {
        if (!scanStatus) return;
        scanStatus.className = 'scan-status ' + (type || '');
        scanStatus.innerHTML = html;
    }

    // ── تبديل الكاميرا ───────────────────────────────────────────────────
    cameraSelect.addEventListener('change', function() {
        if (codeReader) {
            try { codeReader.reset(); } catch(e) {}
        }
        cameraLoading.style.display = 'flex';
        setScanStatus('scanning', '<i class="fas fa-circle-notch fa-spin" style="margin-left:5px;"></i> جاري التبديل...');
        setTimeout(function() {
            cameraLoading.style.display = 'none';
            startDecode(cameraSelect.value);
        }, 400);
    });

    // ── الإدخال اليدوي ───────────────────────────────────────────────────
    function doManualSearch() {
        var code = manualInput.value.trim();
        if (!code) return;
        onBarcodeFound(code);
    }

    manualBtn.addEventListener('click', doManualSearch);
    manualInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); doManualSearch(); }
    });

    // ── أحداث الفتح/الإغلاق ──────────────────────────────────────────────
    openScannerBtn.addEventListener('click', openModal);
    barcodeModalClose.addEventListener('click', closeModal);
    barcodeBackdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && barcodeModal.classList.contains('active')) closeModal();
    });

    // دعم قارئات الباركود الخارجية (USB/Bluetooth) — تلتقط المدخلات السريعة
    var hwBuffer = '', hwTimer = null;
    document.addEventListener('keypress', function(e) {
        if (barcodeModal.classList.contains('active')) return; // المودال مفتوح، تجاهل
        if (document.activeElement && (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA')) return;
        clearTimeout(hwTimer);
        hwBuffer += e.key;
        hwTimer = setTimeout(function() {
            if (hwBuffer.length >= 4) {
                // احتمال كبير أنه مدخل من قارئ باركود خارجي
                if (liveSearchInput) liveSearchInput.value = hwBuffer;
                if (typeof performSearch === 'function') performSearch(hwBuffer);
            }
            hwBuffer = '';
        }, 80);
    });

})();
document.head.appendChild(style);
</script>
@endpush
