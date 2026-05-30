{{-- المسار الكامل: resources/views/products/_product_row.blade.php --}}
{{-- مستخدم من ProductController::index() عند طلبات AJAX (Infinite Scroll) --}}

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
