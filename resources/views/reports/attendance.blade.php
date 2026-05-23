{{-- المسار: resources/views/reports/hr/attendance.blade.php --}}

@extends('layouts.app')

@section('title', __('reports.attendance_report'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.attendance_report') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('reports.attendance_report_desc') }}</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
            العودة للتقارير
        </a>
    </div>

    {{-- Month/Year Filter --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الشهر</label>
                <select name="month" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-400">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($data['month'] == $m)>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">السنة</label>
                <select name="year" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-400">
                    @foreach(range(now()->year - 2, now()->year) as $y)
                        <option value="{{ $y }}" @selected($data['year'] == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-fuchsia-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-fuchsia-700 transition w-full">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 inline-block ml-1"/>
                    عرض التقرير
                </button>
            </div>
        </div>
    </form>

    {{-- Summary Cards --}}
    @php
        $rows = collect($data['rows']);
        $totalPresent = $rows->sum('present_days');
        $totalAbsent  = $rows->sum('absent_days');
        $totalLate    = $rows->sum('late_days');
        $totalLeave   = $rows->sum('leave_days');
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي أيام الحضور</p>
            <p class="text-2xl font-bold text-green-700">{{ $totalPresent }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي أيام الغياب</p>
            <p class="text-2xl font-bold text-red-600">{{ $totalAbsent }}</p>
        </div>
        <div class="bg-white rounded-xl border border-yellow-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي أيام التأخر</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $totalLate }}</p>
        </div>
        <div class="bg-white rounded-xl border border-violet-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي أيام الإجازة</p>
            <p class="text-2xl font-bold text-violet-600">{{ $totalLeave }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">
                تقرير الحضور — {{ \Carbon\Carbon::create($data['year'], $data['month'])->translatedFormat('F Y') }}
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الموظف</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">القسم</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">
                            <span class="text-green-600">حضور</span>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">
                            <span class="text-red-500">غياب</span>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">
                            <span class="text-yellow-600">تأخر</span>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">
                            <span class="text-violet-600">إجازة</span>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">نسبة الحضور</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['rows'] as $row)
                    @php
                        $totalDays = $row['present_days'] + $row['absent_days'] + $row['late_days'] + $row['leave_days'];
                        $attendanceRate = $totalDays > 0 ? round(($row['present_days'] / $totalDays) * 100) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $row['department'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-green-600">{{ $row['present_days'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-red-500">{{ $row['absent_days'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-yellow-600">{{ $row['late_days'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-violet-600">{{ $row['leave_days'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $attendanceRate >= 80 ? 'bg-green-500' : ($attendanceRate >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                         style="width: {{ $attendanceRate }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-600">{{ $attendanceRate }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">لا توجد بيانات حضور لهذا الشهر</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr class="font-semibold">
                        <td colspan="2" class="px-4 py-3 text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 text-center text-green-600">{{ $totalPresent }}</td>
                        <td class="px-4 py-3 text-center text-red-500">{{ $totalAbsent }}</td>
                        <td class="px-4 py-3 text-center text-yellow-600">{{ $totalLate }}</td>
                        <td class="px-4 py-3 text-center text-violet-600">{{ $totalLeave }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Export --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('reports.export-pdf', 'attendance') }}?month={{ $data['month'] }}&year={{ $data['year'] }}"
           class="flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4"/> تصدير PDF
        </a>
    </div>

</div>
@endsection
