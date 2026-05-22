{{-- المسار الكامل: resources/views/suppliers/create.blade.php --}}

@extends('layouts.app')

@section('title', 'إضافة مورد جديد')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('suppliers.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">إضافة مورد جديد</h1>
    </div>

    <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            <h2 class="text-base font-semibold text-gray-700 border-b pb-3">البيانات الأساسية</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- الاسم --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم المورد <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- اسم الشركة --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الشركة</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>

                {{-- الهاتف --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>

                {{-- البريد --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>

                {{-- الرقم الضريبي --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الرقم الضريبي</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>

                {{-- شروط الدفع --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">شروط الدفع <span class="text-red-500">*</span></label>
                    <select name="payment_terms" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                        <option value="cash"   {{ old('payment_terms','cash') === 'cash'   ? 'selected' : '' }}>نقدي</option>
                        <option value="net_7"  {{ old('payment_terms') === 'net_7'  ? 'selected' : '' }}>صافي 7 أيام</option>
                        <option value="net_15" {{ old('payment_terms') === 'net_15' ? 'selected' : '' }}>صافي 15 يوم</option>
                        <option value="net_30" {{ old('payment_terms') === 'net_30' ? 'selected' : '' }}>صافي 30 يوم</option>
                        <option value="net_60" {{ old('payment_terms') === 'net_60' ? 'selected' : '' }}>صافي 60 يوم</option>
                    </select>
                </div>

                {{-- التقييم --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">التقييم <span class="text-red-500">*</span></label>
                    <select name="rating" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ old('rating', 3) == $i ? 'selected' : '' }}>
                                {{ $i }} {{ $i === 1 ? 'نجمة' : 'نجوم' }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            {{-- العنوان --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                <textarea name="address" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">{{ old('address') }}</textarea>
            </div>

            {{-- ملاحظات --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('suppliers.index') }}"
               class="px-5 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg transition">
                إلغاء
            </a>
            <button type="submit"
                    class="px-5 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition">
                حفظ المورد
            </button>
        </div>
    </form>
</div>
@endsection
