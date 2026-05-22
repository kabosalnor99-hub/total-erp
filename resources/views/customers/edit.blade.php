{{-- المسار الكامل: resources/views/customers/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'تعديل العميل: ' . $customer->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">تعديل بيانات العميل</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $customer->name }}</p>
        </div>
        <a href="{{ route('customers.show', $customer) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            رجوع
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- الاسم --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        اسم العميل <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F] @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- نوع العميل --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">نوع العميل <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F]">
                        <option value="individual" {{ old('type', $customer->type) === 'individual' ? 'selected' : '' }}>فرد</option>
                        <option value="company" {{ old('type', $customer->type) === 'company' ? 'selected' : '' }}>شركة</option>
                    </select>
                </div>

                {{-- التصنيف --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">التصنيف <span class="text-red-500">*</span></label>
                    <select name="classification" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F]">
                        <option value="regular" {{ old('classification', $customer->classification) === 'regular' ? 'selected' : '' }}>عادي</option>
                        <option value="vip" {{ old('classification', $customer->classification) === 'vip' ? 'selected' : '' }}>VIP</option>
                        <option value="inactive" {{ old('classification', $customer->classification) === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>

                {{-- اسم الشركة --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الشركة</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F]">
                </div>

                {{-- الرقم الضريبي --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الرقم الضريبي</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F]">
                </div>

                {{-- الهاتف --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F]">
                </div>

                {{-- هاتف بديل --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">هاتف بديل</label>
                    <input type="text" name="phone_alt" value="{{ old('phone_alt', $customer->phone_alt) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F]">
                </div>

                {{-- البريد الإلكتروني --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F]">
                </div>

                {{-- حد الائتمان --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">حد الائتمان (ج.س)</label>
                    <input type="number" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" min="0" step="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F]">
                </div>

                {{-- العنوان --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                    <input type="text" name="address" value="{{ old('address', $customer->address) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F]">
                </div>

                {{-- ملاحظات --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <textarea name="notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-[#00838F] focus:border-[#00838F]">{{ old('notes', $customer->notes) }}</textarea>
                </div>

                {{-- حالة النشاط --}}
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-[#00838F] border-gray-300 rounded focus:ring-[#00838F]">
                        <span class="text-sm font-medium text-gray-700">العميل نشط</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                <a href="{{ route('customers.show', $customer) }}"
                   class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    إلغاء
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-[#00838F] text-white rounded-lg hover:bg-[#005F6B] transition-colors font-medium">
                    حفظ التغييرات
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
