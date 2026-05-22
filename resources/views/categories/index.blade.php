{{-- المسار الكامل: resources/views/categories/index.blade.php --}}

@extends('layouts.app')

@section('title', 'الفئات')

@section('content')
<div class="space-y-6">

    {{-- ─── رأس الصفحة ──────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">إدارة فئات المنتجات</h1>
            <p class="text-sm text-gray-500 mt-1">إجمالي الفئات: {{ $categories->count() }}</p>
        </div>
        @if(auth()->user()->hasPermission('categories.create'))
        <button onclick="document.getElementById('modal-add-category').classList.remove('hidden')"
                class="btn-primary flex items-center gap-2">
            <i class="fas fa-plus"></i>
            إضافة فئة جديدة
        </button>
        @endif
    </div>

    {{-- ─── رسائل النجاح والخطأ ─────────────────────────────────────── --}}
    @if(session('success'))
    <div class="alert-success flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4">
        <i class="fas fa-check-circle text-green-500 text-lg"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert-error flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
        <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- ─── جدول الفئات ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#00838F] text-white">
                        <th class="px-4 py-3 text-right font-semibold">#</th>
                        <th class="px-4 py-3 text-right font-semibold">الفئة</th>
                        <th class="px-4 py-3 text-right font-semibold">الاسم بالإنجليزية</th>
                        <th class="px-4 py-3 text-center font-semibold">الفئة الأب</th>
                        <th class="px-4 py-3 text-center font-semibold">الفئات الفرعية</th>
                        <th class="px-4 py-3 text-center font-semibold">المنتجات</th>
                        <th class="px-4 py-3 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-3 text-center font-semibold">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($category->color)
                                <span class="w-4 h-4 rounded-full flex-shrink-0"
                                      style="background-color: {{ $category->color }}"></span>
                                @endif
                                @if($category->icon)
                                <i class="{{ $category->icon }} text-[#00838F]"></i>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $category->name_ar }}</p>
                                    {{-- الفئات الفرعية --}}
                                    @if($category->children->count() > 0)
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($category->children->take(3) as $child)
                                        <span class="text-xs bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full">
                                            {{ $child->name_ar }}
                                        </span>
                                        @endforeach
                                        @if($category->children->count() > 3)
                                        <span class="text-xs text-gray-400">+{{ $category->children->count() - 3 }} أخرى</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-left" dir="ltr">
                            {{ $category->name_en ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">
                            {{ $category->parent?->name_ar ?? '— رئيسية —' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-semibold text-[#00838F]">{{ $category->children->count() }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('products.index', ['category_id' => $category->id]) }}"
                               class="font-semibold text-[#00838F] hover:underline">
                                {{ $category->products_count }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($category->is_active)
                            <span class="badge-success px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                نشطة
                            </span>
                            @else
                            <span class="badge-danger px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                معطّلة
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                @if(auth()->user()->hasPermission('categories.edit'))
                                <button onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name_ar) }}', '{{ addslashes($category->name_en ?? '') }}', {{ $category->parent_id ?? 'null' }}, '{{ $category->icon ?? '' }}', '{{ $category->color ?? '' }}', {{ $category->sort_order }}, {{ $category->is_active ? 'true' : 'false' }})"
                                        class="text-[#00838F] hover:text-[#005F6B] p-1 rounded transition-colors"
                                        title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif
                                @if(auth()->user()->hasPermission('categories.delete') && $category->products_count == 0 && $category->children->count() == 0)
                                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                      onsubmit="return confirm('هل أنت متأكد من حذف فئة: {{ $category->name_ar }}؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:text-red-700 p-1 rounded transition-colors"
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                            <i class="fas fa-tags text-4xl mb-3 block"></i>
                            لا توجد فئات بعد. أضف فئتك الأولى!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ─── مودال إضافة فئة ──────────────────────────────────────────────── --}}
@if(auth()->user()->hasPermission('categories.create'))
<div id="modal-add-category"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4" dir="rtl">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fas fa-tag text-[#00838F] ml-2"></i>
                إضافة فئة جديدة
            </h2>
            <button onclick="document.getElementById('modal-add-category').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">
                &times;
            </button>
        </div>
        <form method="POST" action="{{ route('categories.store') }}" class="p-5 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        الاسم بالعربية <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name_ar" required
                           value="{{ old('name_ar') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none"
                           placeholder="مثال: أدوات كهربائية">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        الاسم بالإنجليزية
                    </label>
                    <input type="text" name="name_en" dir="ltr"
                           value="{{ old('name_en') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none"
                           placeholder="e.g. Power Tools">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الفئة الأب (اختياري)</label>
                <select name="parent_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none bg-white">
                    <option value="">— فئة رئيسية —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name_ar }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">أيقونة (Font Awesome class)</label>
                    <input type="text" name="icon"
                           value="{{ old('icon') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none"
                           placeholder="fas fa-tools">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اللون</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color"
                               value="{{ old('color', '#00838F') }}"
                               class="h-10 w-16 rounded border border-gray-300 cursor-pointer">
                        <span class="text-xs text-gray-500">لون مميز للفئة</span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ترتيب العرض</label>
                <input type="number" name="sort_order" min="0"
                       value="{{ old('sort_order', 0) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button"
                        onclick="document.getElementById('modal-add-category').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    إلغاء
                </button>
                <button type="submit"
                        class="px-5 py-2 text-sm font-semibold text-white bg-[#00838F] hover:bg-[#005F6B] rounded-lg transition-colors">
                    <i class="fas fa-save ml-1"></i>
                    حفظ الفئة
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ─── مودال تعديل فئة ──────────────────────────────────────────────── --}}
@if(auth()->user()->hasPermission('categories.edit'))
<div id="modal-edit-category"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4" dir="rtl">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fas fa-edit text-[#00838F] ml-2"></i>
                تعديل الفئة
            </h2>
            <button onclick="document.getElementById('modal-edit-category').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">
                &times;
            </button>
        </div>
        <form method="POST" id="edit-category-form" action="" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        الاسم بالعربية <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="edit-name-ar" name="name_ar" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الاسم بالإنجليزية</label>
                    <input type="text" id="edit-name-en" name="name_en" dir="ltr"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الفئة الأب</label>
                <select id="edit-parent-id" name="parent_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none bg-white">
                    <option value="">— فئة رئيسية —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name_ar }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الأيقونة</label>
                    <input type="text" id="edit-icon" name="icon"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اللون</label>
                    <input type="color" id="edit-color" name="color"
                           class="h-10 w-full rounded border border-gray-300 cursor-pointer">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ترتيب العرض</label>
                    <input type="number" id="edit-sort-order" name="sort_order" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="edit-is-active" name="is_active" value="1"
                               class="w-4 h-4 text-[#00838F] rounded">
                        <span class="text-sm font-medium text-gray-700">فئة نشطة</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button"
                        onclick="document.getElementById('modal-edit-category').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    إلغاء
                </button>
                <button type="submit"
                        class="px-5 py-2 text-sm font-semibold text-white bg-[#00838F] hover:bg-[#005F6B] rounded-lg transition-colors">
                    <i class="fas fa-save ml-1"></i>
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function openEditModal(id, nameAr, nameEn, parentId, icon, color, sortOrder, isActive) {
    const form = document.getElementById('edit-category-form');
    form.action = `/categories/${id}`;

    document.getElementById('edit-name-ar').value    = nameAr;
    document.getElementById('edit-name-en').value    = nameEn;
    document.getElementById('edit-icon').value       = icon;
    document.getElementById('edit-color').value      = color || '#00838F';
    document.getElementById('edit-sort-order').value = sortOrder;
    document.getElementById('edit-is-active').checked = isActive;

    const parentSelect = document.getElementById('edit-parent-id');
    parentSelect.value = parentId || '';

    document.getElementById('modal-edit-category').classList.remove('hidden');
}

// إغلاق المودال بالضغط خارجه
['modal-add-category', 'modal-edit-category'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
});
</script>
@endpush
