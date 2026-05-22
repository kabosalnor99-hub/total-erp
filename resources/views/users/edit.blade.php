@extends('layouts.app')

@section('title', 'تعديل مستخدم')
@section('page-title', 'تعديل: ' . $user->name)

@section('breadcrumb')
    <i class="fa fa-chevron-left text-xs mx-1"></i>
    <a href="{{ route('users.index') }}" class="hover:text-primary">المستخدمون</a>
    <i class="fa fa-chevron-left text-xs mx-1"></i>
    <span class="text-gray-700">تعديل</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

        {{-- معلومات المستخدم --}}
        <div class="flex items-center gap-4 mb-6 pb-5 border-b">
            <img src="{{ $user->avatar_url }}" class="w-14 h-14 rounded-full object-cover border-2 border-primary">
            <div>
                <p class="font-bold text-gray-800">{{ $user->name }}</p>
                <p class="text-sm text-gray-500">{{ $user->role_name }} • انضم {{ $user->created_at->diffForHumans() }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">الاسم الكامل <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('name') border-red-400 @enderror">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('email') border-red-400 @enderror">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">رقم الهاتف</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('phone') border-red-400 @enderror">
                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">الدور <span class="text-red-500">*</span></label>
                <select name="role_id"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white @error('role_id') border-red-400 @enderror">
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id', $user->roles->first()?->id) == $role->id ? 'selected' : '' }}>
                        {{ $role->display_name }}
                    </option>
                    @endforeach
                </select>
                @error('role_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">الحالة</label>
                <select name="status"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white">
                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>معطّل</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">صورة جديدة (اختياري)</label>
                <input type="file" name="avatar" accept="image/*"
                    class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-white file:font-medium file:text-sm hover:file:bg-primary-dark">
                @error('avatar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="flex-1 bg-primary text-white font-medium py-3 rounded-xl hover:bg-primary-dark transition text-sm">
                    <i class="fa fa-save ml-2"></i> حفظ التعديلات
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
