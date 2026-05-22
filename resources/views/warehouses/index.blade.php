{{-- المسار الكامل: resources/views/warehouses/index.blade.php --}}

@extends('layouts.app')

@section('title', 'المستودعات')

@section('content')
<div class="space-y-6">

    {{-- ─── رأس الصفحة ──────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">إدارة المستودعات</h1>
            <p class="text-sm text-gray-500 mt-1">إجمالي المستودعات: {{ $warehouses->count() }}</p>
        </div>
        @if(auth()->user()->hasPermission('warehouses.create'))
        <button onclick="document.getElementById('modal-add-warehouse').classList.remove('hidden')"
                class="btn-primary flex items-center gap-2">
            <i class="fas fa-plus"></i>
            إضافة مستودع جديد
        </button>
        @endif
    </div>

    {{-- ─── رسائل ────────────────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4">
        <i class="fas fa-check-circle text-green-500"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
        <i class="fas fa-exclamation-circle text-red-500"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- ─── بطاقات المستودعات ───────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($warehouses as $warehouse)
        <div class="bg-white rounded-xl shadow-sm border {{ $warehouse->is_default ? 'border-[#00838F]' : 'border-gray-100' }} overflow-hidden hover:shadow-md transition-shadow">

            {{-- رأس البطاقة --}}
            <div class="px-5 py-4 flex items-center justify-between
                        {{ $warehouse->is_active ? 'bg-gradient-to-l from-[#00838F]/5 to-transparent' : 'bg-gray-50' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-[#00838F]/10 flex items-center justify-center">
                        <i class="fas fa-warehouse text-[#00838F] text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">{{ $warehouse->name }}</h3>
                        @if($warehouse->code)
                        <p class="text-xs text-gray-500 font-mono">{{ $warehouse->code }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($warehouse->is_default)
                    <span class="text-xs bg-[#00838F] text-white px-2 py-0.5 rounded-full font-medium">
                        افتراضي
                    </span>
                    @endif
                    @if(!$warehouse->is_active)
                    <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">
                        معطّل
                    </span>
                    @endif
                </div>
            </div>

            {{-- تفاصيل المستودع --}}
            <div class="px-5 py-3 space-y-2">
                @if($warehouse->location)
                <div class="flex items-start gap-2 text-sm text-gray-600">
                    <i class="fas fa-map-marker-alt text-gray-400 mt-0.5 w-4"></i>
                    <span>{{ $warehouse->location }}</span>
                </div>
                @endif
                @if($warehouse->manager_name)
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-user text-gray-400 w-4"></i>
                    <span>{{ $warehouse->manager_name }}</span>
                </div>
                @endif
                @if($warehouse->phone)
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-phone text-gray-400 w-4"></i>
                    <span dir="ltr">{{ $warehouse->phone }}</span>
                </div>
                @endif
                @if($warehouse->notes)
                <div class="flex items-start gap-2 text-sm text-gray-500">
                    <i class="fas fa-sticky-note text-gray-400 mt-0.5 w-4"></i>
                    <span>{{ Str::limit($warehouse->notes, 60) }}</span>
                </div>
                @endif
            </div>

            {{-- إحصائيات + أزرار --}}
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-1 text-sm text-gray-500">
                    <i class="fas fa-exchange-alt text-[#00838F]"></i>
                    <span>{{ number_format($warehouse->stock_movements_count) }} حركة</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('warehouses.movements', $warehouse) }}"
                       class="text-xs text-[#00838F] hover:underline">
                        <i class="fas fa-list-ul"></i>
                        الحركات
                    </a>
                    @if(auth()->user()->hasPermission('warehouses.edit'))
                    <button onclick="openEditWarehouse(
                                {{ $warehouse->id }},
                                '{{ addslashes($warehouse->name) }}',
                                '{{ addslashes($warehouse->code ?? '') }}',
                                '{{ addslashes($warehouse->location ?? '') }}',
                                '{{ addslashes($warehouse->manager_name ?? '') }}',
                                '{{ addslashes($warehouse->phone ?? '') }}',
                                {{ $warehouse->is_default ? 'true' : 'false' }},
                                {{ $warehouse->is_active ? 'true' : 'false' }},
                                '{{ addslashes($warehouse->notes ?? '') }}'
                            )"
                            class="text-xs text-blue-600 hover:underline">
                        <i class="fas fa-edit"></i>
                        تعديل
                    </button>
                    @endif
                    @if(auth()->user()->hasPermission('warehouses.delete') && $warehouse->stock_movements_count == 0 && !$warehouse->is_default)
                    <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}"
                          onsubmit="return confirm('هل أنت متأكد من حذف مستودع: {{ $warehouse->name }}؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline">
                            <i class="fas fa-trash"></i>
                            حذف
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-xl p-12 text-center text-gray-400">
            <i class="fas fa-warehouse text-5xl mb-3 block"></i>
            لا توجد مستودعات بعد. أضف مستودعك الأول!
        </div>
        @endforelse
    </div>

</div>

{{-- ─── مودال إضافة مستودع ───────────────────────────────────────────── --}}
@if(auth()->user()->hasPermission('warehouses.create'))
<div id="modal-add-warehouse"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4" dir="rtl">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fas fa-warehouse text-[#00838F] ml-2"></i>
                إضافة مستودع جديد
            </h2>
            <button onclick="document.getElementById('modal-add-warehouse').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('warehouses.store') }}" class="p-5 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        اسم المستودع <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none"
                           placeholder="مثال: المستودع الرئيسي">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">كود المستودع</label>
                    <input type="text" name="code"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none"
                           placeholder="مثال: WH-01">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الموقع / العنوان</label>
                <input type="text" name="location"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none"
                       placeholder="مثال: الكلاكلة، الخرطوم">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">مسؤول المستودع</label>
                    <input type="text" name="manager_name"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" dir="ltr"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none resize-none"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" id="add-is-default"
                       class="w-4 h-4 text-[#00838F] rounded">
                <label for="add-is-default" class="text-sm text-gray-700 cursor-pointer">
                    تعيين كمستودع افتراضي
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button"
                        onclick="document.getElementById('modal-add-warehouse').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    إلغاء
                </button>
                <button type="submit"
                        class="px-5 py-2 text-sm font-semibold text-white bg-[#00838F] hover:bg-[#005F6B] rounded-lg transition-colors">
                    <i class="fas fa-save ml-1"></i>
                    حفظ المستودع
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ─── مودال تعديل مستودع ───────────────────────────────────────────── --}}
@if(auth()->user()->hasPermission('warehouses.edit'))
<div id="modal-edit-warehouse"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4" dir="rtl">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fas fa-edit text-[#00838F] ml-2"></i>
                تعديل المستودع
            </h2>
            <button onclick="document.getElementById('modal-edit-warehouse').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <form method="POST" id="edit-warehouse-form" action="" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        اسم المستودع <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="edit-wh-name" name="name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الكود</label>
                    <input type="text" id="edit-wh-code" name="code"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الموقع</label>
                <input type="text" id="edit-wh-location" name="location"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المسؤول</label>
                    <input type="text" id="edit-wh-manager" name="manager_name"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الهاتف</label>
                    <input type="text" id="edit-wh-phone" name="phone" dir="ltr"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                <textarea id="edit-wh-notes" name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#00838F] focus:border-[#00838F] outline-none resize-none"></textarea>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="edit-wh-default" name="is_default" value="1"
                           class="w-4 h-4 text-[#00838F] rounded">
                    <span class="text-sm text-gray-700">مستودع افتراضي</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="edit-wh-active" name="is_active" value="1"
                           class="w-4 h-4 text-[#00838F] rounded">
                    <span class="text-sm text-gray-700">مستودع نشط</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button"
                        onclick="document.getElementById('modal-edit-warehouse').classList.add('hidden')"
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
function openEditWarehouse(id, name, code, location, manager, phone, isDefault, isActive, notes) {
    document.getElementById('edit-warehouse-form').action = `/warehouses/${id}`;
    document.getElementById('edit-wh-name').value    = name;
    document.getElementById('edit-wh-code').value    = code;
    document.getElementById('edit-wh-location').value = location;
    document.getElementById('edit-wh-manager').value = manager;
    document.getElementById('edit-wh-phone').value   = phone;
    document.getElementById('edit-wh-notes').value   = notes;
    document.getElementById('edit-wh-default').checked = isDefault;
    document.getElementById('edit-wh-active').checked  = isActive;

    document.getElementById('modal-edit-warehouse').classList.remove('hidden');
}

['modal-add-warehouse', 'modal-edit-warehouse'].forEach(id => {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
});
</script>
@endpush
