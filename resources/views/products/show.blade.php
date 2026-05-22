{{-- المسار الكامل: resources/views/products/show.blade.php --}}

@extends('layouts.app')

@section('title', $product->name_ar)

@section('content')
<div class="space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('products.index') }}" class="btn-icon text-gray-500">
                <i class="fas fa-arrow-right"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $product->name_ar }}</h1>
                <p class="text-sm text-gray-500 font-mono">{{ $product->sku }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('products.edit'))
            <a href="{{ route('products.edit', $product) }}" class="btn-secondary">
                <i class="fas fa-edit ml-1"></i> تعديل
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── بيانات المنتج ────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="card">
                <div class="flex gap-6">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name_ar }}"
                         class="w-32 h-32 object-contain rounded-xl border border-gray-200 shrink-0">
                    <div class="flex-1 grid grid-cols-2 gap-x-6 gap-y-3">
                        <div>
                            <span class="text-xs text-gray-400 block">الفئة</span>
                            <span class="font-medium">{{ $product->category?->name_ar ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block">النوع</span>
                            <span class="font-medium">{{ $product->type_label }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block">العلامة التجارية</span>
                            <span class="font-medium">{{ $product->brand ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block">الوحدة</span>
                            <span class="font-medium">{{ $product->unit }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block">سعر الشراء</span>
                            <span class="font-bold text-gray-700">{{ number_format($product->purchase_price, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block">سعر البيع</span>
                            <span class="font-bold text-teal-600 text-lg">{{ number_format($product->sale_price, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block">هامش الربح</span>
                            <span class="font-bold text-green-600">{{ $product->profit_margin }}%</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block">الباركود</span>
                            <span class="font-mono text-sm">{{ $product->barcode ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                @if($product->description)
                <p class="text-gray-600 text-sm mt-4 pt-4 border-t border-gray-100">
                    {{ $product->description }}
                </p>
                @endif
            </div>

            {{-- بطاقات المخزون حسب المستودع --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($warehouses as $wh)
                <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                    <div class="text-xs text-gray-400 mb-1">{{ $wh->name }}</div>
                    <div class="text-2xl font-bold {{ $wh->current_stock > 0 ? 'text-teal-600' : 'text-red-500' }}">
                        {{ number_format($wh->current_stock) }}
                    </div>
                    <div class="text-xs text-gray-400">{{ $product->unit }}</div>
                </div>
                @endforeach
            </div>

            {{-- سجل حركة المخزون --}}
            <div class="card">
                <h2 class="card-title">سجل حركة المخزون</h2>
                <div class="overflow-x-auto">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>النوع</th>
                                <th>الكمية</th>
                                <th>قبل</th>
                                <th>بعد</th>
                                <th>المستودع</th>
                                <th>المستخدم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $mv)
                            <tr>
                                <td class="text-xs text-gray-500">{{ $mv->created_at->format('Y-m-d H:i') }}</td>
                                <td><span class="badge-{{ $mv->type_color }}">{{ $mv->type_label }}</span></td>
                                <td class="font-bold {{ in_array($mv->type, ['in','return_in']) ? 'text-green-600' : 'text-red-600' }}">
                                    {{ in_array($mv->type, ['in','return_in']) ? '+' : '-' }}{{ $mv->quantity }}
                                </td>
                                <td class="text-gray-500">{{ $mv->quantity_before }}</td>
                                <td class="font-medium">{{ $mv->quantity_after }}</td>
                                <td>{{ $mv->warehouse?->name ?? '—' }}</td>
                                <td class="text-sm text-gray-500">{{ $mv->user?->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-400 py-6">لا توجد حركات مخزون</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($movements->hasPages())
                <div class="mt-4">{{ $movements->links() }}</div>
                @endif
            </div>

        </div>

        {{-- ─── تسوية المخزون ────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- بطاقة الحالة --}}
            <div class="card text-center">
                <div class="text-5xl font-black {{ $product->quantity > 0 ? 'text-teal-600' : 'text-red-500' }} mb-1">
                    {{ number_format($product->quantity) }}
                </div>
                <div class="text-gray-500 text-sm">{{ $product->unit }} — إجمالي المخزون</div>
                <div class="mt-3">
                    <span class="badge-{{ $product->stock_status_color }} text-sm">
                        {{ $product->stock_status_label }}
                    </span>
                </div>
                <div class="text-xs text-gray-400 mt-2">
                    حد الطلب الأدنى: {{ $product->reorder_point }} {{ $product->unit }}
                </div>
            </div>

            {{-- نموذج تسوية المخزون --}}
            @if(auth()->user()->hasPermission('stock.adjust'))
            <div class="card">
                <h2 class="card-title">تسوية المخزون</h2>
                <form method="POST" action="{{ route('products.adjust', $product) }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="form-label">المستودع</label>
                            <select name="warehouse_id" class="input-field w-full" required>
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->current_stock }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">الكمية الفعلية الجديدة</label>
                            <input type="number" name="new_quantity" min="0"
                                   class="input-field w-full" required>
                        </div>
                        <div>
                            <label class="form-label">سبب التسوية <span class="text-red-500">*</span></label>
                            <input type="text" name="reason"
                                   class="input-field w-full"
                                   placeholder="مثال: جرد دوري، تلف..." required>
                        </div>
                        <button type="submit" class="btn-warning w-full justify-center"
                                onclick="return confirm('هل أنت متأكد من تسوية المخزون؟')">
                            <i class="fas fa-balance-scale ml-1"></i> تنفيذ التسوية
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
