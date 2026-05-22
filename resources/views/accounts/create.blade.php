{{-- المسار الكامل: resources/views/accounts/create.blade.php --}}
@extends('layouts.app')
@section('title', 'إضافة حساب جديد')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('accounts.index') }}" class="text-gray-400 hover:text-primary transition">
            <i class="fa fa-arrow-right text-lg"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">إضافة حساب جديد</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- اسم الحساب بالعربية --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الحساب (عربي) <span class="text-red-500">*</span></label>
                    <input type="text" name="name_ar" value="{{ old('name_ar') }}" required
                        class="w-full border @error('name_ar') border-red-400 @else border-gray-200 @enderror rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    @error('name_ar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- اسم الحساب بالإنجليزية --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الحساب (إنجليزي)</label>
                    <input type="text" name="name_en" value="{{ old('name_en') }}" dir="ltr"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                {{-- نوع الحساب --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">نوع الحساب <span class="text-red-500">*</span></label>
                    <select name="type" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">اختر...</option>
                        <option value="asset"     {{ old('type')=='asset'     ? 'selected':'' }}>أصول</option>
                        <option value="liability" {{ old('type')=='liability' ? 'selected':'' }}>خصوم</option>
                        <option value="equity"    {{ old('type')=='equity'    ? 'selected':'' }}>حقوق ملكية</option>
                        <option value="revenue"   {{ old('type')=='revenue'   ? 'selected':'' }}>إيرادات</option>
                        <option value="expense"   {{ old('type')=='expense'   ? 'selected':'' }}>مصروفات</option>
                    </select>
                </div>

                {{-- طبيعة الحساب --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">طبيعة الحساب <span class="text-red-500">*</span></label>
                    <select name="normal_balance" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="debit"  {{ old('normal_balance')=='debit'  ? 'selected':'' }}>مدين</option>
                        <option value="credit" {{ old('normal_balance')=='credit' ? 'selected':'' }}>دائن</option>
                    </select>
                </div>

                {{-- الحساب الأب --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">الحساب الأب (اختياري)</label>
                    <select name="parent_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">— بدون حساب أب (حساب جذر) —</option>
                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id')==$parent->id ? 'selected':'' }}>
                            {{ $parent->code }} — {{ $parent->name_ar }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- الرصيد الافتتاحي --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الرصيد الافتتاحي</label>
                    <input type="number" name="opening_balance" value="{{ old('opening_balance', 0) }}" min="0" step="0.01"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                {{-- نوع الرصيد الافتتاحي --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">نوع الرصيد الافتتاحي</label>
                    <select name="opening_balance_type"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="debit"  {{ old('opening_balance_type')=='debit'  ? 'selected':'' }}>مدين</option>
                        <option value="credit" {{ old('opening_balance_type')=='credit' ? 'selected':'' }}>دائن</option>
                    </select>
                </div>

                {{-- حساب تفصيلي --}}
                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_leaf" value="1"
                            {{ old('is_leaf', true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-primary focus:ring-primary/30">
                        <span class="text-sm text-gray-700">حساب تفصيلي (قابل لإدخال القيود)</span>
                    </label>
                </div>

                {{-- الوصف --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">وصف (اختياري)</label>
                    <textarea name="description" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="submit"
                    class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition font-medium">
                    <i class="fa fa-save ml-2"></i> حفظ الحساب
                </button>
                <a href="{{ route('accounts.index') }}"
                    class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
