{{-- المسار الكامل: resources/views/attendance/employee-report.blade.php --}}

@extends('layouts.app')

@section('title', 'تقرير حضور موظف')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">تقرير حضور: {{ $employee->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $employee->employee_number }} — {{ $employee->job_title }}
                @if($employee->department)· {{ $employee->department->name }}@endif
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('attendance.index') }}"
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">
                <i class="fa fa-arrow-right me-1"></i> رجوع
            </a>
            <button onclick="window.print()"
                    class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition font-medium">
                <i class="fa fa-print me-1"></i> طباعة
            </button>
        </div>
    </div>

    {{-- فلتر الشهر --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        @php
            $monthNames = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
                           7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
        @endphp
        <form method="GET" action="{{ route('attendance.employee-report', $employee) }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">الشهر</label>
                <select name="month" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    @foreach($monthNames as $num => $name)
                        <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">السنة</label>
                <select name="year" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition">
                <i class="fa fa-filter me-1"></i> عرض
            </button>
        </form>
    </div>

    {{-- بطاقات الإحصاء --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $records->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">إجمالي الأيام</p>
        </div>
        <div class="bg-green-50 rounded-xl border border-green-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['present'] }}</p>
            <p class="text-xs text-green-500 mt-1">حاضر</p>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-200 p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $stats['absent'] }}</p>
            <p class="text-xs text-red-400 mt-1">غائب</p>
        </div>
        <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['late'] }}</p>
            <p class="text-xs text-yellow-500 mt-1">متأخر</p>
        </div>
        <div class="bg-blue-50 rounded-xl border border-blue-200 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['on_leave'] }}</p>
            <p class="text-xs text-blue-500 mt-1">إجازة</p>
        </div>
        <div class="bg-teal-50 rounded-xl border border-teal-200 p-4 text-center">
            @php
                $lateH = intdiv($stats['total_late_min'], 60);
                $lateM = $stats['total_late_min'] % 60;
            @endphp
            <p class="text-2xl font-bold text-teal-600">{{ $lateH > 0 ? $lateH.'س' : '' }}{{ $lateM }}د</p>
            <p class="text-xs text-teal-500 mt-1">إجمالي التأخير</p>
        </div>
    </div>

    {{-- شريط الحضور المرئي --}}
    @if($records->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">نظرة عامة على الشهر</h2>
        <div class="flex flex-wrap gap-1">
            @foreach($records as $rec)
            @php
                $color = match($rec->status) {
                    'present'  => 'bg-green-400 text-white',
                    'absent'   => 'bg-red-400 text-white',
                    'late'     => 'bg-yellow-400 text-white',
                    'on_leave' => 'bg-blue-400 text-white',
                    'holiday'  => 'bg-gray-300 text-gray-600',
                    default    => 'bg-gray-200 text-gray-500',
                };
            @endphp
            <div class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-semibold {{ $color }}"
                 title="{{ $rec->date->format('Y/m/d') }} — {{ $rec->status_label }}">
                {{ $rec->date->day }}
            </div>
            @endforeach
        </div>
        <div class="flex flex-wrap gap-4 mt-3 text-xs text-gray-500">
            <span><span class="inline-block w-3 h-3 rounded bg-green-400 me-1"></span>حاضر</span>
            <span><span class="inline-block w-3 h-3 rounded bg-red-400 me-1"></span>غائب</span>
            <span><span class="inline-block w-3 h-3 rounded bg-yellow-400 me-1"></span>متأخر</span>
            <span><span class="inline-block w-3 h-3 rounded bg-blue-400 me-1"></span>إجازة</span>
            <span><span class="inline-block w-3 h-3 rounded bg-gray-300 me-1"></span>إجازة رسمية</span>
        </div>
    </div>
    @endif

    {{-- جدول التفاصيل --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">
                <i class="fa fa-calendar-days text-teal-600 me-2"></i>
                تفاصيل {{ $monthNames[$month] ?? $month }} {{ $year }}
            </h2>
        </div>

        @if($records->isEmpty())
        <div class="p-12 text-center">
            <i class="fa fa-calendar-xmark text-3xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">لا توجد سجلات حضور لهذا الشهر</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-teal-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold">التاريخ</th>
                        <th class="px-4 py-3 text-right font-semibold">اليوم</th>
                        <th class="px-4 py-3 text-center font-semibold">وقت الدخول</th>
                        <th class="px-4 py-3 text-center font-semibold">وقت الخروج</th>
                        <th class="px-4 py-3 text-center font-semibold">ساعات العمل</th>
                        <th class="px-4 py-3 text-center font-semibold">التأخير</th>
                        <th class="px-4 py-3 text-center font-semibold">إضافي</th>
                        <th class="px-4 py-3 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-3 text-center font-semibold">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($records as $rec)
                    <tr class="hover:bg-gray-50 {{ $rec->status === 'absent' ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $rec->date->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            @php
                                $days = ['Sunday'=>'الأحد','Monday'=>'الاثنين','Tuesday'=>'الثلاثاء','Wednesday'=>'الأربعاء','Thursday'=>'الخميس','Friday'=>'الجمعة','Saturday'=>'السبت'];
                            @endphp
                            {{ $days[$rec->date->format('l')] ?? $rec->date->format('l') }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $rec->check_in ? \Carbon\Carbon::parse($rec->check_in)->format('H:i') : '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $rec->check_out ? \Carbon\Carbon::parse($rec->check_out)->format('H:i') : '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">
                            {{ $rec->working_hours > 0 ? number_format($rec->working_hours, 1).'س' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="{{ $rec->late_minutes > 0 ? 'text-yellow-600 font-semibold' : 'text-gray-400' }}">
                                {{ $rec->late_minutes_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="{{ $rec->overtime_minutes > 0 ? 'text-teal-600 font-semibold' : 'text-gray-400' }}">
                                {{ $rec->overtime_minutes_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $rec->status_color }}">
                                {{ $rec->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $rec->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection

@push('styles')
<style>
@media print {
    nav, aside, .no-print, button, form { display: none !important; }
    body { background: white; }
}
</style>
@endpush
