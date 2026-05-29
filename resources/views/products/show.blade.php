{{-- المسار الكامل: resources/views/products/show.blade.php --}}

@extends('layouts.app')

@section('title', $product->name_ar)

@section('content')
<div class="space-y-6">

    {{-- رأس الصفحة --}}
    <div class="bg-gradient-to-r from-teal-600 to-teal-700 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('products.index') }}" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold">{{ $product->name_ar }}</h1>
                    <p class="text-teal-100 text-sm font-mono">{{ $product->sku }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                @if(auth()->user()->hasPermission('products.edit'))
                <a href="{{ route('products.edit', $product) }}" class="bg-white text-teal-700 px-4 py-2 rounded-lg font-medium hover:bg-teal-50 transition flex items-center gap-2">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── بيانات المنتج ────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- بطاقة الصورة والمعلومات الأساسية --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex flex-col md:flex-row gap-6 p-6">
                    <div class="flex-shrink-0">
                        <div class="w-48 h-48 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl flex items-center justify-center border-2 border-gray-200 overflow-hidden">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name_ar }}"
                                 class="w-full h-full object-contain p-4"
                                 onerror="this.src='{{ asset('images/no-product.png') }}'">
                        </div>
                    </div>
                    <div class="flex-1 space-y-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">{{ $product->name_ar }}</h2>
                            @if($product->name_en)
                            <p class="text-gray-500 text-sm">{{ $product->name_en }}</p>
                            @endif
                        </div>
                        @if($product->description)
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $product->description }}</p>
                        @endif
                        <div class="flex flex-wrap gap-2">
                            @if($product->is_active)
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">نشط</span>
                            @else
                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-medium">غير نشط</span>
                            @endif
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">{{ $product->type_label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- بطاقات المعلومات الأساسية --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <div class="flex items-center gap-2 text-blue-600 mb-2">
                        <i class="fas fa-box"></i>
                        <span class="text-xs font-medium">الفئة</span>
                    </div>
                    <div class="font-bold text-gray-800">{{ $product->category?->name_ar ?? '—' }}</div>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                    <div class="flex items-center gap-2 text-purple-600 mb-2">
                        <i class="fas fa-tag"></i>
                        <span class="text-xs font-medium">العلامة التجارية</span>
                    </div>
                    <div class="font-bold text-gray-800">{{ $product->brand ?? '—' }}</div>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                    <div class="flex items-center gap-2 text-orange-600 mb-2">
                        <i class="fas fa-ruler"></i>
                        <span class="text-xs font-medium">الوحدة</span>
                    </div>
                    <div class="font-bold text-gray-800">{{ $product->unit }}</div>
                </div>
                <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl p-4 border border-pink-200">
                    <div class="flex items-center gap-2 text-pink-600 mb-2">
                        <i class="fas fa-barcode"></i>
                        <span class="text-xs font-medium">الباركود</span>
                    </div>
                    <div class="font-bold text-gray-800 font-mono text-sm">{{ $product->barcode ?? '—' }}</div>
                </div>
            </div>

            {{-- ★ بطاقات الأسعار — USD + SDG ──────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-dollar-sign text-teal-600"></i>
                    الأسعار
                    @php $currentRate = \App\Models\ExchangeRate::currentRate(); @endphp
                    @if($currentRate)
                    <span class="mr-auto text-xs bg-teal-50 text-teal-600 border border-teal-200 px-3 py-1 rounded-full font-normal">
                        سعر الصرف: 1 USD = {{ number_format($currentRate, 0) }} ج.س
                    </span>
                    @endif
                </h3>

                {{-- سعر البيع --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    {{-- بطاقة سعر البيع --}}
                    <div class="rounded-2xl overflow-hidden border border-teal-200 shadow-sm">
                        <div class="bg-gradient-to-r from-teal-500 to-teal-600 px-4 py-2 flex items-center gap-2">
                            <i class="fas fa-tag text-white text-sm"></i>
                            <span class="text-white text-sm font-medium">سعر البيع</span>
                        </div>
                        <div class="p-4 bg-white">
                            {{-- USD --}}
                            <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-sm">$</span>
                                    <span class="text-gray-500 text-sm">دولار أمريكي</span>
                                </div>
                                <span class="text-2xl font-black text-gray-800 font-mono">
                                    {{ $product->price_usd ? number_format($product->price_usd, 2) : number_format($product->sale_price / ($currentRate ?: 1), 2) }}
                                </span>
                            </div>
                            {{-- SDG --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center text-teal-700 font-bold text-xs">ج.س</span>
                                    <span class="text-gray-500 text-sm">جنيه سوداني</span>
                                </div>
                                <span class="text-2xl font-black text-teal-600 font-mono">
                                    {{ number_format($product->sale_price, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- بطاقة سعر الشراء --}}
                    <div class="rounded-2xl overflow-hidden border border-blue-200 shadow-sm">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-2 flex items-center gap-2">
                            <i class="fas fa-shopping-cart text-white text-sm"></i>
                            <span class="text-white text-sm font-medium">سعر الشراء</span>
                        </div>
                        <div class="p-4 bg-white">
                            {{-- USD --}}
                            <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-sm">$</span>
                                    <span class="text-gray-500 text-sm">دولار أمريكي</span>
                                </div>
                                <span class="text-2xl font-black text-gray-800 font-mono">
                                    {{ $product->purchase_price_usd ? number_format($product->purchase_price_usd, 2) : number_format($product->purchase_price / ($currentRate ?: 1), 2) }}
                                </span>
                            </div>
                            {{-- SDG --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold text-xs">ج.س</span>
                                    <span class="text-gray-500 text-sm">جنيه سوداني</span>
                                </div>
                                <span class="text-2xl font-black text-blue-600 font-mono">
                                    {{ number_format($product->purchase_price, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- هامش الربح --}}
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-chart-line text-lg"></i>
                        </div>
                        <div>
                            <div class="text-green-100 text-xs">هامش الربح</div>
                            <div class="text-sm font-medium">
                                @php
                                    $profitUsd = ($product->price_usd ?? 0) - ($product->purchase_price_usd ?? 0);
                                @endphp
                                @if($profitUsd > 0)
                                ربح: ${{ number_format($profitUsd, 2) }} للوحدة
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-4xl font-black">{{ $product->profit_margin }}%</div>
                </div>
            </div>

            {{-- بطاقات المخزون حسب المستودع --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-warehouse text-teal-600"></i>
                    المخزون حسب المستودع
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($warehouses as $wh)
                    <div class="bg-gradient-to-br {{ $wh->current_stock > 0 ? 'from-teal-50 to-teal-100 border-teal-200' : 'from-red-50 to-red-100 border-red-200' }} rounded-xl p-4 border">
                        <div class="text-xs text-gray-500 mb-1">{{ $wh->name }}</div>
                        <div class="text-3xl font-bold {{ $wh->current_stock > 0 ? 'text-teal-600' : 'text-red-500' }}">
                            {{ number_format($wh->current_stock) }}
                        </div>
                        <div class="text-xs text-gray-400 mt-1">{{ $product->unit }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- سجل حركة المخزون --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-history text-teal-600"></i>
                    سجل حركة المخزون
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-right text-xs font-medium text-gray-500 px-4 py-3 rounded-r-lg">التاريخ</th>
                                <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">النوع</th>
                                <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">الكمية</th>
                                <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">قبل</th>
                                <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">بعد</th>
                                <th class="text-right text-xs font-medium text-gray-500 px-4 py-3">المستودع</th>
                                <th class="text-right text-xs font-medium text-gray-500 px-4 py-3 rounded-l-lg">المستخدم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $mv)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="text-xs text-gray-500 px-4 py-3">{{ $mv->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ $mv->type === 'in' ? 'bg-green-100 text-green-700' :
                                           ($mv->type === 'out' ? 'bg-red-100 text-red-700' :
                                           ($mv->type === 'adjust' ? 'bg-yellow-100 text-yellow-700' :
                                           'bg-blue-100 text-blue-700')) }}">
                                        {{ $mv->type_label }}
                                    </span>
                                </td>
                                <td class="font-bold px-4 py-3 {{ in_array($mv->type, ['in','return_in']) ? 'text-green-600' : 'text-red-600' }}">
                                    {{ in_array($mv->type, ['in','return_in']) ? '+' : '-' }}{{ $mv->quantity }}
                                </td>
                                <td class="text-gray-500 px-4 py-3">{{ $mv->quantity_before ?? '—' }}</td>
                                <td class="font-medium px-4 py-3">{{ $mv->quantity_after ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $mv->warehouse?->name ?? '—' }}</td>
                                <td class="text-sm text-gray-500 px-4 py-3">{{ $mv->user?->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-400 py-8">
                                    <i class="fas fa-inbox text-4xl mb-2 block"></i>
                                    لا توجد حركات مخزون
                                </td>
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

        {{-- ─── الشريط الجانبي ────────────────────────────────────────── --}}
        <div class="space-y-6">

            {{-- بطاقة حالة المخزون --}}
            <div class="bg-gradient-to-br {{ $product->quantity > 0 ? 'from-teal-500 to-teal-600' : 'from-red-500 to-red-600' }} rounded-2xl p-6 text-white shadow-lg">
                <div class="text-center">
                    <div class="text-6xl font-black mb-2">{{ number_format($product->quantity) }}</div>
                    <div class="text-white/80 text-sm mb-4">{{ $product->unit }} — إجمالي المخزون</div>
                    <div class="inline-block bg-white/20 backdrop-blur-sm rounded-full px-4 py-2 text-sm font-medium">
                        {{ $product->stock_status_label }}
                    </div>
                    <div class="text-white/60 text-xs mt-4">
                        حد الطلب الأدنى: {{ $product->reorder_point }} {{ $product->unit }}
                    </div>
                </div>
            </div>

            {{-- ★ بطاقة ملخص الأسعار الجانبية --}}
            @php $rate = \App\Models\ExchangeRate::currentRate(); @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-coins text-yellow-500"></i>
                    ملخص الأسعار
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500 text-sm">البيع بالدولار</span>
                        <span class="font-bold text-green-600 font-mono">
                            ${{ number_format($product->price_usd ?? ($product->sale_price / ($rate ?: 1)), 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500 text-sm">البيع بالجنيه</span>
                        <span class="font-bold text-teal-600 font-mono">
                            {{ number_format($product->sale_price, 0) }} ج.س
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500 text-sm">الشراء بالدولار</span>
                        <span class="font-bold text-blue-600 font-mono">
                            ${{ number_format($product->purchase_price_usd ?? ($product->purchase_price / ($rate ?: 1)), 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500 text-sm">الشراء بالجنيه</span>
                        <span class="font-bold text-blue-500 font-mono">
                            {{ number_format($product->purchase_price, 0) }} ج.س
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-500 text-sm">سعر الصرف الحالي</span>
                        <span class="font-bold text-orange-600 font-mono text-sm">
                            @if($rate)
                                1$ = {{ number_format($rate, 0) }}
                            @else
                                <span class="text-red-400 text-xs">غير محدد</span>
                            @endif
                        </span>
                    </div>
                </div>
                @if($rate)
                <a href="{{ route('exchange-rates.index') }}" class="mt-4 w-full flex items-center justify-center gap-2 text-xs text-teal-600 hover:text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg px-3 py-2 transition">
                    <i class="fas fa-exchange-alt"></i> إدارة أسعار الصرف
                </a>
                @else
                <a href="{{ route('exchange-rates.index') }}" class="mt-4 w-full flex items-center justify-center gap-2 text-xs text-red-600 bg-red-50 hover:bg-red-100 rounded-lg px-3 py-2 transition">
                    <i class="fas fa-exclamation-triangle"></i> لم يُحدَّد سعر الصرف بعد
                </a>
                @endif
            </div>

            {{-- نموذج تسوية المخزون --}}
            @if(auth()->user()->hasPermission('stock.adjust'))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-balance-scale text-teal-600"></i>
                    تسوية المخزون
                </h3>
                <form method="POST" action="{{ route('products.adjust', $product) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المستودع</label>
                            <select name="warehouse_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent" required>
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->current_stock }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الكمية الفعلية الجديدة</label>
                            <input type="number" name="new_quantity" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">سبب التسوية <span class="text-red-500">*</span></label>
                            <input type="text" name="reason"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                   placeholder="مثال: جرد دوري، تلف..." required>
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-3 rounded-lg font-medium hover:from-orange-600 hover:to-orange-700 transition flex items-center justify-center gap-2"
                                onclick="return confirm('هل أنت متأكد من تسوية المخزون؟')">
                            <i class="fas fa-balance-scale"></i> تنفيذ التسوية
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- معلومات إضافية --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-teal-600"></i>
                    معلومات إضافية
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500 text-sm">تاريخ الإنشاء</span>
                        <span class="font-medium text-gray-800">{{ $product->created_at->format('Y-m-d') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500 text-sm">آخر تحديث</span>
                        <span class="font-medium text-gray-800">{{ $product->updated_at->format('Y-m-d') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-500 text-sm">تم الإنشاء بواسطة</span>
                        <span class="font-medium text-gray-800">{{ $product->creator?->name ?? '—' }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
