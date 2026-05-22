{{-- المسار الكامل: resources/views/leaves/show.blade.php --}}

@extends('layouts.app')

@section('title', 'تفاصيل طلب الإجازة')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('leaves.index') }}" class="text-gray-400 hover:text-teal-600 transition">
                <i class="fa fa-arrow-right text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">طلب إجازة</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $leave->employee->name }}</p>
            </div>
        </div>
        <span class="px-3 py-1.5 rounded-full text-sm font-semibold {{ $leave->status_color }}">
            {{ $leave->status_label }}
        </span>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
        <i class="fa fa-circle-check me-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm">
        <i class="fa fa-circle-xmark me-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- بيانات الموظف --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 text-xl font-bold">
                {{ mb_substr($leave->employee->name, 0, 1) }}
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-lg">{{ $leave->employee->name }}</p>
                <p class="text-sm text-gray-500">{{ $leave->employee->employee_number }} — {{ $leave->employee->job_title }}</p>
                @if($leave->employee->department)
                    <p class="text-xs text-teal-600 mt-1"><i class="fa fa-building me-1"></i>{{ $leave->employee->department->name }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- تفاصيل الطلب --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-teal-600">
            <h2 class="font-semibold text-white"><i class="fa fa-file-alt me-2"></i>تفاصيل الطلب</h2>
        </div>
        <div class="divide-y divide-gray-100">
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">نوع الإجازة</span>
                <span class="text-sm font-medium text-gray-800">{{ $leave->type_label }}</span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">تاريخ البداية</span>
                <span class="text-sm font-medium text-gray-800">{{ $leave->start_date->format('Y/m/d') }}</span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">تاريخ النهاية</span>
                <span class="text-sm font-medium text-gray-800">{{ $leave->end_date->format('Y/m/d') }}</span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">عدد الأيام</span>
                <span class="text-sm font-bold text-teal-600">{{ $leave->days }} يوم</span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">الحالة</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $leave->status_color }}">
                    {{ $leave->status_label }}
                </span>
            </div>
            @if($leave->reason)
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">السبب</span>
                <span class="text-sm text-gray-700">{{ $leave->reason }}</span>
            </div>
            @endif
            @if($leave->status === 'approved' && $leave->approvedBy)
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">اعتمد بواسطة</span>
                <span class="text-sm font-medium text-green-700">{{ $leave->approvedBy->name }}</span>
            </div>
            @endif
            @if($leave->status === 'rejected' && $leave->rejection_reason)
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">سبب الرفض</span>
                <span class="text-sm text-red-600">{{ $leave->rejection_reason }}</span>
            </div>
            @endif
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">تاريخ الطلب</span>
                <span class="text-sm text-gray-600">{{ $leave->created_at->format('Y/m/d H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- رصيد الإجازات --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">رصيد إجازات الموظف</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $leave->employee->annual_leave_balance }}</p>
                <p class="text-xs text-blue-400 mt-1">رصيد الإجازة السنوية (يوم)</p>
            </div>
            <div class="bg-green-50 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-green-600">{{ $leave->employee->sick_leave_balance }}</p>
                <p class="text-xs text-green-400 mt-1">رصيد الإجازة المرضية (يوم)</p>
            </div>
        </div>
    </div>

    {{-- أزرار الإجراءات --}}
    @if($leave->status === 'pending')
    <div class="flex gap-3">
        @canPermission('leaves.approve')
        <form action="{{ route('leaves.approve', $leave) }}" method="POST" class="flex-1">
            @csrf
            <button type="submit"
                    class="w-full px-4 py-3 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition">
                <i class="fa fa-check me-1"></i> اعتماد الطلب
            </button>
        </form>
        <div class="flex-1" x-data="{ open: false }">
            <button @click="open = true"
                    class="w-full px-4 py-3 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700 transition">
                <i class="fa fa-times me-1"></i> رفض الطلب
            </button>
            <div x-show="open" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm">
                    <h3 class="font-bold text-gray-800 mb-3">سبب الرفض</h3>
                    <form action="{{ route('leaves.reject', $leave) }}" method="POST">
                        @csrf
                        <textarea name="rejection_reason" rows="3" required
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none resize-none mb-4"
                                  placeholder="اكتب سبب الرفض..."></textarea>
                        <div class="flex gap-2">
                            <button type="button" @click="open = false"
                                    class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">إلغاء</button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition">رفض</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcanPermission
    </div>
    @endif

    {{-- حذف --}}
    @if($leave->status !== 'approved')
    @canPermission('leaves.delete')
    <div class="flex justify-end">
        <form action="{{ route('leaves.destroy', $leave) }}" method="POST"
              onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">
            @csrf @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm hover:bg-red-100 transition">
                <i class="fa fa-trash me-1"></i> حذف الطلب
            </button>
        </form>
    </div>
    @endcanPermission
    @endif

</div>
@endsection
