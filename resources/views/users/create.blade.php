@extends('layouts.app')

@section('title', 'إضافة مستخدم')
@section('page-title', 'إضافة مستخدم جديد')

@section('breadcrumb')
    <i class="fa fa-chevron-left text-xs mx-1"></i>
    <a href="{{ route('users.index') }}" class="hover:text-primary">المستخدمون</a>
    <i class="fa fa-chevron-left text-xs mx-1"></i>
    <span class="text-gray-700">إضافة جديد</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- الاسم --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">الاسم الكامل <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('name') border-red-400 @enderror">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- البريد الإلكتروني --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('email') border-red-400 @enderror">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- رقم الهاتف --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">رقم الهاتف</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('phone') border-red-400 @enderror">
                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- الدور --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">الدور <span class="text-red-500">*</span></label>
                <select name="role_id"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white @error('role_id') border-red-400 @enderror">
                    <option value="">اختر الدور</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->display_name }}
                    </option>
                    @endforeach
                </select>
                @error('role_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- كلمة المرور --}}
            <div x-data="{ show: false }">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">كلمة المرور <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="password"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('password') border-red-400 @enderror">
                    <button type="button" @click="show=!show" class="absolute top-1/2 -translate-y-1/2 left-3 text-gray-400">
                        <i :class="show ? 'fa-eye-slash' : 'fa-eye'" class="fa text-xs"></i>
                    </button>
                </div>
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- تأكيد كلمة المرور --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">تأكيد كلمة المرور <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            {{-- الحالة --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">الحالة</label>
                <select name="status"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>معطّل</option>
                </select>
            </div>

            {{-- الصورة --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">الصورة الشخصية</label>
                <input type="file" name="avatar" accept="image/*"
                    class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-white file:font-medium file:text-sm hover:file:bg-primary-dark">
                @error('avatar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- الأزرار --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="flex-1 bg-primary text-white font-medium py-3 rounded-xl hover:bg-primary-dark transition text-sm">
                    <i class="fa fa-save ml-2"></i> حفظ المستخدم
                </button>
                <a href="{{ route('users.index') }}"
                    class="flex-1 text-center bg-gray-100 text-gray-600 font-medium py-3 rounded-xl hover:bg-gray-200 transition text-sm">
                    إلغاء
                </a>
            </div>

        </form>
    </div>
</div>
@endsection
