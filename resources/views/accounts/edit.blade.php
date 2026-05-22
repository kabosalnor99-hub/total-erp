{{-- المسار الكامل: resources/views/accounts/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'تعديل حساب: ' . $account->code)

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('accounts.index') }}" class="text-gray-400 hover:text-primary transition">
            <i class="fa fa-arrow-right text-lg"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">تعديل حساب: <span class="text-primary">{{ $account->code }}</span></h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('accounts.update', $account) }}">
            @csrf @method('PUT')

            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mb-4 text-sm text-gray-600">
                <i class="fa fa-info-circle text-primary ml-2"></i>
                الكود: <strong>{{ $account->code }}</strong> — النوع: <strong>{{ $account->type_label }}</strong>
                (لا يمكن تغيير الكود أو النوع بعد الإنشاء)
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الحساب (عربي) <span class="text-red-500">*</span></label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $account->name_ar) }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الحساب (إنجليزي)</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $account->name_en) }}" dir="ltr"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">طبيعة الحساب <span class="text-red-500">*</span></label>
                    <select name="normal_balance" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="debit"  {{ old('normal_balance', $account->normal_balance)=='debit'  ? 'selected':'' }}>مدين</option>
                        <option value="credit" {{ old('normal_balance', $account->normal_balance)=='credit' ? 'selected':'' }}>دائن</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الرصيد الافتتاحي</label>
                    <input type="number" name="opening_balance" value="{{ old('opening_balance', $account->opening_balance) }}" min="0" step="0.01"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">نوع الرصيد الافتتاحي</label>
                    <select name="opening_balance_type"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="debit"  {{ old('opening_balance_type', $account->opening_balance_type)=='debit'  ? 'selected':'' }}>مدين</option>
                        <option value="credit" {{ old('opening_balance_type', $account->opening_balance_type)=='credit' ? 'selected':'' }}>دائن</option>
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $account->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-primary">
                        <span class="text-sm text-gray-700">الحساب نشط</span>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">وصف</label>
                    <textarea name="description" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">{{ old('description', $account->description) }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="submit"
                    class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition font-medium">
                    <i class="fa fa-save ml-2"></i> حفظ التعديلات
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
