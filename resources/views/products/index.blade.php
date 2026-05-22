{{-- المسار الكامل: resources/views/products/index.blade.php --}}

@extends('layouts.app')

@section('title', 'المنتجات')

@section('content')
<div class="space-y-6">

    {{-- ─── رأس الصفحة ──────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">إدارة المنتجات</h1>
            <p class="text-sm text-gray-500 mt-1">إجمالي المنتجات: {{ number_format($stats['total']) }}</p>
        </div>
        @if(auth()->user()->hasPermission('products.create'))
        <a href="{{ route('products.create') }}"
           class="btn-primary flex items-center gap-2">
            <i class="fas fa-plus"></i>
            إضافة منتج جديد
        </a>
        @endif
    </div>

    {{-- ─── بطاقات الإحصائيات ───────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('products.index') }}"
           class="stat-card bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:border-teal-300 transition-all">
            <div class="text-3xl font-bold text-teal-600">{{ number_format($stats['total']) }}</div>
            <div class="text-sm text-gray-500 mt-1">إجمالي المنتجات</div>
        </a>
        <a href="{{ route('products.index', ['status' => 'active']) }}"
           class="stat-card bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:border-green-300 transition-all">
            <div class="text-3xl font-bold text-green-600">{{ number_format($stats['active']) }}</div>
            <div class="text-sm text-gray-500 mt-1">منتجات نشطة</div>
        </a>
        <a href="{{ route('products.index', ['filter' => 'critical']) }}"
           class="stat-card bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:border-yellow-300 transition-all">
            <div class="text-3xl font-bold text-yellow-600">{{ number_format($stats['critical']) }}</div>
            <div class="text-sm text-gray-500 mt-1">مخزون حرج</div>
        </a>
        <a href="{{ route('products.index', ['filter' => 'out_of_stock']) }}"
           class="stat-card bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:border-red-300 transition-all">
            <div class="text-3xl font-bold text-red-600">{{ number_format($stats['out_of_stock']) }}</div>
            <div class="text-sm text-gray-500 mt-1">نفد المخزون</div>
        </a>
    </div>

    {{-- ─── فلاتر البحث ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('products.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="بحث بالاسم أو الكود أو الباركود..."
                       class="input-field w-full">
            </div>
            <div>
                <select name="category_id" class="input-field w-full">
                    <option value="">كل الفئات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="type" class="input-field w-full">
                    <option value="">كل الأنواع</option>
                    <option value="power_tools" {{ request('type') === 'power_tools' ? 'selected' : '' }}>أدوات كهربائية</option>
                    <option value="hand_tools"  {{ request('type') === 'hand_tools'  ? 'selected' : '' }}>أدوات يدوية</option>
                    <option value="equipment"   {{ request('type') === 'equipment'   ? 'selected' : '' }}>معدات</option>
                    <option value="spare_parts" {{ request('type') === 'spare_parts' ? 'selected' : '' }}>قطع غيار</option>
                    <option value="other"       {{ request('type') === 'other'       ? 'selected' : '' }}>أخرى</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1">
                    <i class="fas fa-search ml-1"></i> بحث
                </button>
                <a href="{{ route('products.index') }}" class="btn-secondary px-3">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ─── جدول المنتجات ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>الكود (SKU)</th>
                        <th>الفئة</th>
                        <th>سعر الشراء</th>
                        <th>سعر البيع</th>
                        <th>المخزون</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        {{-- المنتج --}}
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ $product->image_url }}"
                                     alt="{{ $product->name_ar }}"
                                     class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $product->name_ar }}</div>
                                    @if($product->name_en)
                                    <div class="text-xs text-gray-400">{{ $product->name_en }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        {{-- الكود --}}
                        <td>
                            <span class="font-mono text-sm text-teal-700 bg-teal-50 px-2 py-1 rounded">
                                {{ $product->sku }}
                            </span>
                        </td>
                        {{-- الفئة --}}
                        <td class="text-gray-600">
                            {{ $product->category?->name_ar ?? '—' }}
                        </td>
                        {{-- سعر الشراء --}}
                        <td class="text-gray-700 font-medium">
                            {{ number_format($product->purchase_price, 2) }}
                        </td>
                        {{-- سعر البيع --}}
                        <td class="font-semibold text-teal-700">
                            {{ number_format($product->sale_price, 2) }}
                        </td>
                        {{-- المخزون --}}
                        <td>
                            <span class="badge-{{ $product->stock_status_color }} font-bold">
                                {{ number_format($product->quantity) }} {{ $product->unit }}
                            </span>
                            @if($product->stock_status !== 'available')
                            <div class="text-xs text-gray-400 mt-0.5">{{ $product->stock_status_label }}</div>
                            @endif
                        </td>
                        {{-- الحالة --}}
                        <td>
                            @if($product->is_active)
                                <span class="badge-success">نشط</span>
                            @else
                                <span class="badge-danger">موقوف</span>
                            @endif
                        </td>
                        {{-- الإجراءات --}}
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('products.show', $product) }}"
                                   class="btn-icon text-teal-600" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->hasPermission('products.edit'))
                                <a href="{{ route('products.edit', $product) }}"
                                   class="btn-icon text-blue-600" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermission('products.delete'))
                                <form method="POST" action="{{ route('products.destroy', $product) }}"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon text-red-600" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400">
                            <i class="fas fa-box-open text-4xl mb-3 block"></i>
                            لا توجد منتجات مطابقة للبحث
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
