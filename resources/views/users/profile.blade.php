@extends('layouts.app')

@section('title', 'الملف الشخصي')
@section('page-title', 'الملف الشخصي')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- معلومات شخصية --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-700 mb-5 flex items-center gap-2 text-sm">
            <i class="fa fa-user text-primary"></i> المعلومات الشخصية
        </h3>

        <div class="flex items-center gap-4 mb-6">
            <img src="{{ $user->avatar_url }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-primary shadow-sm">
            <div>
                <p class="font-bold text-gray-800">{{ $user->name }}</p>
                <p class="text-sm text-gray-500">{{ $user->role_name }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">الاسم الكامل</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">الصورة الشخصية</label>
                <input type="file" name="avatar" accept="image/*"
                    class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-white file:font-medium file:text-sm hover:file:bg-primary-dark">
            </div>

            <button type="submit"
                class="w-full bg-primary text-white font-medium py-3 rounded-xl hover:bg-primary-dark transition text-sm">
                <i class="fa fa-save ml-2"></i> حفظ المعلومات
            </button>
        </form>
    </div>

    {{-- تغيير كلمة المرور --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-700 mb-5 flex items-center gap-2 text-sm">
            <i class="fa fa-lock text-primary"></i> تغيير كلمة المرور
        </h3>

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">كلمة المرور الحالية</label>
                <input type="password" name="current_password"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('current_password') border-red-400 @enderror">
                @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">كلمة المرور الجديدة</label>
                <input type="password" name="password"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('password') border-red-400 @enderror">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">تأكيد كلمة المرور الجديدة</label>
                <input type="password" name="password_confirmation"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <button type="submit"
                class="w-full bg-gray-800 text-white font-medium py-3 rounded-xl hover:bg-gray-900 transition text-sm">
                <i class="fa fa-key ml-2"></i> تغيير كلمة المرور
            </button>
        </form>
    </div>

</div>
@endsection
