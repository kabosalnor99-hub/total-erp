@extends('layouts.app')

@section('title', 'المستخدمون')
@section('page-title', 'إدارة المستخدمين')

@section('breadcrumb')
    <i class="fa fa-chevron-left text-xs mx-1"></i>
    <span class="text-gray-700">المستخدمون</span>
@endsection

@section('content')

{{-- شريط الأدوات --}}
<div class="flex flex-col sm:flex-row gap-3 mb-5">

    {{-- البحث --}}
    <form method="GET" action="{{ route('users.index') }}" class="flex-1 flex gap-2">
        <div class="relative flex-1">
            <span class="absolute top-1/2 -translate-y-1/2 right-3 text-gray-400 text-sm">
                <i class="fa fa-search"></i>
            </span>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="بحث بالاسم أو البريد أو الهاتف..."
                class="w-full pr-9 pl-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary">
        </div>

        <select name="role" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white">
            <option value="">كل الأدوار</option>
            @foreach($roles as $role)
            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                {{ $role->display_name }}
            </option>
            @endforeach
        </select>

        <select name="status" class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white">
            <option value="">كل الحالات</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>معطّل</option>
        </select>

        <button type="submit" class="bg-primary text-white px-4 py-2.5 rounded-xl text-sm hover:bg-primary-dark transition">
            <i class="fa fa-filter"></i>
        </button>
        @if(request()->hasAny(['search','role','status']))
        <a href="{{ route('users.index') }}" class="bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-sm hover:bg-gray-300 transition">
            <i class="fa fa-xmark"></i>
        </a>
        @endif
    </form>

    {{-- زر الإضافة --}}
    @can('users.create')
    <a href="{{ route('users.create') }}"
       class="flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-primary-dark transition shadow-sm">
        <i class="fa fa-plus"></i>
        مستخدم جديد
    </a>
    @endcan
</div>

{{-- الجدول --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-gray-600 text-xs uppercase">
                    <th class="px-5 py-4 text-right font-semibold">#</th>
                    <th class="px-5 py-4 text-right font-semibold">المستخدم</th>
                    <th class="px-5 py-4 text-right font-semibold">الدور</th>
                    <th class="px-5 py-4 text-right font-semibold">الحالة</th>
                    <th class="px-5 py-4 text-right font-semibold">آخر دخول</th>
                    <th class="px-5 py-4 text-right font-semibold">تاريخ الإنشاء</th>
                    <th class="px-5 py-4 text-right font-semibold">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4 text-gray-400 text-xs">{{ $loop->iteration }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_url }}" class="w-9 h-9 rounded-full object-cover border-2 border-gray-100" alt="{{ $user->name }}">
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email ?? $user->phone }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <span class="bg-teal-50 text-teal-700 text-xs px-2.5 py-1 rounded-full font-medium">
                            {{ $user->role_name }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        @if($user->status === 'active')
                        <span class="flex items-center gap-1.5 text-green-700 text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> نشط
                        </span>
                        @else
                        <span class="flex items-center gap-1.5 text-red-600 text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span> معطّل
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-xs text-gray-500">
                        {{ $user->last_login_at?->diffForHumans() ?? 'لم يدخل بعد' }}
                    </td>
                    <td class="px-5 py-4 text-xs text-gray-500">
                        {{ $user->created_at->format('Y/m/d') }}
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('users.show', $user) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="عرض">
                                <i class="fa fa-eye text-xs"></i>
                            </a>
                            @can('users.edit')
                            <a href="{{ route('users.edit', $user) }}"
                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-yellow-50 text-yellow-600 hover:bg-yellow-100 transition" title="تعديل">
                                <i class="fa fa-pen text-xs"></i>
                            </a>
                            @endcan
                            @can('users.delete')
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="حذف">
                                    <i class="fa fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12 text-gray-400">
                        <i class="fa fa-users text-4xl mb-3 block text-gray-200"></i>
                        لا يوجد مستخدمون
                        @if(request()->hasAny(['search','role','status']))
                        <br><a href="{{ route('users.index') }}" class="text-primary text-xs mt-1 inline-block">مسح الفلاتر</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="px-5 py-4 border-t">
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection
