@extends('layouts.app')

@section('title', $user->name)
@section('page-title', 'بيانات المستخدم')

@section('breadcrumb')
    <i class="fa fa-chevron-left text-xs mx-1"></i>
    <a href="{{ route('users.index') }}" class="hover:text-primary">المستخدمون</a>
    <i class="fa fa-chevron-left text-xs mx-1"></i>
    <span class="text-gray-700">{{ $user->name }}</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- بطاقة المستخدم --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-primary shadow-sm">
                <div>
                    <h2 class="text-xl font-black text-gray-800">{{ $user->name }}</h2>
                    <span class="bg-teal-100 text-teal-700 text-xs px-2.5 py-1 rounded-full font-medium">
                        {{ $user->role_name }}
                    </span>
                    <div class="flex items-center gap-2 mt-2">
                        @if($user->status === 'active')
                        <span class="flex items-center gap-1 text-green-700 text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> نشط
                        </span>
                        @else
                        <span class="flex items-center gap-1 text-red-600 text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span> معطّل
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            @can('users.edit')
            <a href="{{ route('users.edit', $user) }}"
               class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-sm hover:bg-primary-dark transition">
                <i class="fa fa-pen"></i> تعديل
            </a>
            @endcan
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6 pt-5 border-t">
            <div>
                <p class="text-xs text-gray-400 mb-1">البريد الإلكتروني</p>
                <p class="text-sm text-gray-700 font-medium">{{ $user->email ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">رقم الهاتف</p>
                <p class="text-sm text-gray-700 font-medium">{{ $user->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">تاريخ الانضمام</p>
                <p class="text-sm text-gray-700 font-medium">{{ $user->created_at->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">آخر دخول</p>
                <p class="text-sm text-gray-700 font-medium">{{ $user->last_login_at?->diffForHumans() ?? 'لم يدخل بعد' }}</p>
            </div>
        </div>
    </div>

    {{-- سجل النشاط --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b bg-gray-50">
            <h3 class="font-bold text-gray-700 text-sm flex items-center gap-2">
                <i class="fa fa-clock-rotate-left text-primary"></i>
                سجل النشاط (آخر 20 عملية)
            </h3>
        </div>
        <div class="divide-y">
            @forelse($logs as $log)
            <div class="flex items-start gap-3 px-5 py-3">
                <div class="w-8 h-8 rounded-full bg-teal-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fa fa-{{ $log->action === 'login' ? 'right-to-bracket' : ($log->action === 'deleted' ? 'trash' : 'pen') }} text-primary text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-700">{{ $log->description }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->format('Y/m/d H:i') }} • {{ $log->ip_address }}</p>
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-gray-400 text-sm">
                <i class="fa fa-history text-3xl mb-2 block text-gray-200"></i>
                لا يوجد نشاط مسجّل
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
