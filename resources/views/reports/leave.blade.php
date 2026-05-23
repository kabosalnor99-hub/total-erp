{{-- المسار: resources/views/reports/hr/leave.blade.php --}}

@extends('layouts.app')

@section('title', __('reports.leave_report'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.leave_report') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('reports.leave_report_desc') }}</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
            العودة للتقارير
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from', $data['date_from']) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to', $data['date_to']) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-violet-700 transition">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 inline-block ml-1"/>
                    تصفية
                </button>
                <a href="{{ route('reports.leave') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">إعادة تعيين</a>
            </div>
        </div>
    </form>

    {{-- Summary Cards --}}
    @php
        $approved = $data['rows']->where('status', 'approved')->count();
        $pending  = $data['rows']->where('status', 'pending')->count();
        $rejected = $data['rows']->where('status', 'rejected')->count();
        $totalDays = $data['rows']->sum(fn($l) => \Carbon\Carbon::parse($l->start_date)->diffInDays(\Carbon\Carbon::parse($l->end_date)) + 1);
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الطلبات</p>
            <p class="text-2xl font-bold text-gray-800">{{ $data['rows']->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-xs text-gray-500 mb-1">موافق عليها</p>
            <p class="text-2xl font-bold text-green-700">{{ $approved }}</p>
        </div>
        <div class="bg-white rounded-xl border border-yellow-200 p-4">
            <p class="text-xs text-gray-500 mb-1">قيد الانتظار</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $pending }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي أيام الإجازة</p>
            <p class="text-2xl font-bold text-violet-700">{{ $totalDays }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الموظف</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">نوع الإجازة</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">تاريخ البداية</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">تاريخ النهاية</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">عدد الأيام</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">السبب</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['rows'] as $leave)
                    @php
                        $days = \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $leave->employee->full_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $leave->leave_type ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ \Carbon\Carbon::parse($leave->start_date)->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ \Carbon\Carbon::parse($leave->end_date)->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-violet-700">{{ $days }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $leave->reason ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($leave->status === 'approved')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">موافق</span>
                            @elseif($leave->status === 'pending')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">انتظار</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">مرفوض</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">لا توجد إجازات في هذه الفترة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Export --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('reports.export-pdf', 'leave') }}?{{ http_build_query(request()->all()) }}"
           class="flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4"/> تصدير PDF
        </a>
    </div>

</div>
@endsection
