{{-- المسار: resources/views/customers/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة عميل جديد')

@section('content')
<div class="space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">إضافة عميل جديد</h1>
            <p class="text-sm text-gray-500 mt-1">أدخل بيانات العميل الجديد</p>
        </div>
        <a href="{{ route('customers.index') }}"
           class="flex items-center gap-2 text-gray-600 hover:text-gray-800 px-4 py-2 border rounded-lg transition">
            <i class="fa fa-arrow-right"></i>
            <span>رجوع</span>
        </a>
    </div>

    {{-- رسائل الخطأ --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('customers.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- البيانات الأساسية --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                    <h2 class="font-bold text-gray-700 border-b pb-2">البيانات الأساسية</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم العميل <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">نوع العميل <span class="text-red-500">*</span></label>
                            <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="individual" {{ old('type')=='individual'?'selected':'' }}>فرد</option>
                                <option value="company" {{ old('type')=='company'?'selected':'' }}>شركة</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">هاتف بديل</label>
                            <input type="text" name="phone_alt" value="{{ old('phone_alt') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم الشركة</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                            <input type="text" name="address" value="{{ old('address') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الرقم الضريبي</label>
                            <input type="text" name="tax_number" value="{{ old('tax_number') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">حد الائتمان (SDG)</label>
                            <input type="number" name="credit_limit" value="{{ old('credit_limit', 0) }}" min="0" step="0.01"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                </div>

                {{-- ملاحظات --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-bold text-gray-700 border-b pb-2 mb-4">ملاحظات</h2>
                    <textarea name="notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                              placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- التصنيف والإعدادات --}}
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
                    <h2 class="font-bold text-gray-700 border-b pb-2">التصنيف</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تصنيف العميل <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            @foreach(['regular'=>['label'=>'عادي','color'=>'gray'],'vip'=>['label'=>'VIP','color'=>'yellow'],'inactive'=>['label'=>'غير نشط','color'=>'red']] as $val=>$opt)
                            <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50">
                                <input type="radio" name="classification" value="{{ $val }}"
                                       {{ old('classification','regular')===$val ? 'checked' : '' }}
                                       class="text-primary focus:ring-primary">
                                <span class="text-sm font-medium text-gray-700">{{ $opt['label'] }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6">
                    <button type="submit"
                            class="w-full bg-primary text-white py-3 rounded-lg hover:bg-primary-dark transition font-medium flex items-center justify-center gap-2">
                        <i class="fa fa-save"></i>
                        حفظ العميل
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
