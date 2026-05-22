{{-- المسار الكامل: resources/views/attendance/show.blade.php --}}

@extends('layouts.app')

@section('title', 'تفاصيل سجل الحضور')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">سجل حضور</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $attendance->date->format('Y/m/d') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('attendance.index') }}"
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">
                <i class="fa fa-arrow-right me-1"></i> رجوع
            </a>
            @canPermission('attendance.edit')
            <a href="{{ route('attendance.edit', $attendance) }}"
               class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition">
                <i class="fa fa-pen me-1"></i> تعديل
            </a>
            @endcanPermission
        </div>
    </div>

    {{-- بطاقة الموظف --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 text-xl font-bold">
                {{ mb_substr($attendance->employee->name, 0, 1) }}
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-lg">{{ $attendance->employee->name }}</p>
                <p class="text-sm text-gray-500">{{ $attendance->employee->employee_number }} — {{ $attendance->employee->job_title }}</p>
                @if($attendance->employee->department)
                <p class="text-xs text-teal-600 mt-1"><i class="fa fa-building me-1"></i>{{ $attendance->employee->department->name }}</p>
                @endif
            </div>
            <div class="mr-auto">
                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $attendance->status_color }}">
                    {{ $attendance->status_label }}
                </span>
            </div>
        </div>
    </div>

    {{-- تفاصيل الحضور --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-teal-600">
            <h2 class="font-semibold text-white"><i class="fa fa-clock me-2"></i>تفاصيل اليوم</h2>
        </div>
        <div class="divide-y divide-gray-100">
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">التاريخ</span>
                <span class="text-sm font-medium text-gray-800">{{ $attendance->date->format('Y/m/d') }}</span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">وقت الدخول</span>
                <span class="text-sm font-medium text-gray-800">
                    {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '—' }}
                </span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">وقت الخروج</span>
                <span class="text-sm font-medium text-gray-800">
                    {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '—' }}
                </span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">ساعات العمل</span>
                <span class="text-sm font-medium text-teal-600">
                    {{ $attendance->working_hours > 0 ? number_format($attendance->working_hours, 2).' ساعة' : '—' }}
                </span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">التأخير</span>
                <span class="text-sm font-medium {{ $attendance->late_minutes > 0 ? 'text-yellow-600' : 'text-gray-400' }}">
                    {{ $attendance->late_minutes_label }}
                </span>
            </div>
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">العمل الإضافي</span>
                <span class="text-sm font-medium {{ $attendance->overtime_minutes > 0 ? 'text-green-600' : 'text-gray-400' }}">
                    {{ $attendance->overtime_minutes_label }}
                </span>
            </div>
            @if($attendance->notes)
            <div class="flex px-5 py-3">
                <span class="text-sm text-gray-500 w-40">ملاحظات</span>
                <span class="text-sm text-gray-700">{{ $attendance->notes }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- روابط سريعة --}}
    <div class="flex gap-3">
        <a href="{{ route('attendance.employee-report', $attendance->employee) }}"
           class="flex-1 text-center px-4 py-3 bg-teal-50 border border-teal-200 text-teal-700 rounded-xl text-sm hover:bg-teal-100 transition">
            <i class="fa fa-chart-line block text-xl mb-1"></i>
            تقرير حضور الموظف
        </a>
        <a href="{{ route('employees.show', $attendance->employee) }}"
           class="flex-1 text-center px-4 py-3 bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-sm hover:bg-gray-100 transition">
            <i class="fa fa-user block text-xl mb-1"></i>
            ملف الموظف
        </a>
        @canPermission('attendance.delete')
        <form action="{{ route('attendance.destroy', $attendance) }}" method="POST"
              onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="flex-1 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm hover:bg-red-100 transition">
                <i class="fa fa-trash block text-xl mb-1"></i>
                حذف السجل
            </button>
        </form>
        @endcanPermission
    </div>

</div>
@endsection
