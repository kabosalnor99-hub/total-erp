{{-- المسار الكامل: resources/views/attendance/index.blade.php --}}

@extends('layouts.app')

@section('title', 'سجل الحضور والغياب')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">سجل الحضور والغياب</h1>
            <p class="text-sm text-gray-500 mt-1">عرض يومي: {{ \Carbon\Carbon::parse($date)->locale('ar')->isoFormat('dddd، D MMMM YYYY') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('attendance.create') }}"
               class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition font-medium">
                <i class="fa fa-plus me-1"></i> تسجيل حضور اليوم
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
        <i class="fa fa-circle-check me-2"></i>{{ session('success') }}
    </div>
    @endif

    {{-- إحصائيات اليوم --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي الموظفين</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-green-50 rounded-xl border border-green-200 p-4 text-center">
            <p class="text-xs text-green-600 mb-1">حاضر</p>
            <p class="text-2xl font-bold text-green-700">{{ $stats['present'] }}</p>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-200 p-4 text-center">
            <p class="text-xs text-red-600 mb-1">غائب</p>
            <p class="text-2xl font-bold text-red-700">{{ $stats['absent'] }}</p>
        </div>
        <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-4 text-center">
            <p class="text-xs text-yellow-600 mb-1">متأخر</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $stats['late'] }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl border border-blue-200 p-4 text-center">
            <p class="text-xs text-blue-600 mb-1">في إجازة</p>
            <p class="text-2xl font-bold text-blue-700">{{ $stats['on_leave'] }}</p>
        </div>
    </div>

    {{-- فلاتر --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="view" value="daily">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">التاريخ</label>
                <input type="date" name="date" value="{{ $date }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">القسم</label>
                <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                    <option value="">الكل</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition">
                <i class="fa fa-search me-1"></i> عرض
            </button>
            {{-- التنقل بين الأيام --}}
            <a href="{{ route('attendance.index', array_merge(request()->all(), ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()])) }}"
               class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">
                <i class="fa fa-chevron-right"></i>
            </a>
            <a href="{{ route('attendance.index', array_merge(request()->all(), ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()])) }}"
               class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">
                <i class="fa fa-chevron-left"></i>
            </a>
            {{-- عرض شهري --}}
            <a href="{{ route('attendance.index', ['view' => 'monthly', 'month' => now()->month, 'year' => now()->year]) }}"
               class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
                <i class="fa fa-calendar-days me-1"></i> العرض الشهري
            </a>
        </form>
    </div>

    {{-- جدول الحضور --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($records->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <i class="fa fa-calendar-xmark text-5xl mb-3 opacity-30"></i>
            <p class="text-lg font-medium">لا توجد سجلات لهذا اليوم</p>
            <p class="text-sm mt-1">
                <a href="{{ route('attendance.create') }}" class="text-teal-600 hover:underline">سجّل حضور الموظفين</a>
            </p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-teal-600 text-white text-right">
                        <th class="px-4 py-3 font-medium">الموظف</th>
                        <th class="px-4 py-3 font-medium">القسم</th>
                        <th class="px-4 py-3 font-medium">الحالة</th>
                        <th class="px-4 py-3 font-medium">وقت الحضور</th>
                        <th class="px-4 py-3 font-medium">وقت الانصراف</th>
                        <th class="px-4 py-3 font-medium">تأخير</th>
                        <th class="px-4 py-3 font-medium">أوفرتايم</th>
                        <th class="px-4 py-3 font-medium">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($records as $record)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $record->employee->name }}</div>
                            <div class="text-xs text-gray-400">{{ $record->employee->employee_number }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $record->employee->department?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = ['present'=>'green','absent'=>'red','late'=>'yellow','on_leave'=>'blue','holiday'=>'gray'];
                                $statusLabels = ['present'=>'حاضر','absent'=>'غائب','late'=>'متأخر','on_leave'=>'إجازة','holiday'=>'عطلة'];
                                $sc = $statusColors[$record->status] ?? 'gray';
                            @endphp
                            <span class="px-2 py-1 bg-{{ $sc }}-100 text-{{ $sc }}-700 rounded-full text-xs font-medium">
                                {{ $statusLabels[$record->status] ?? $record->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600" dir="ltr">{{ $record->check_in ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600" dir="ltr">{{ $record->check_out ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($record->late_minutes > 0)
                                <span class="text-yellow-600 font-medium">{{ $record->late_minutes }} د</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($record->overtime_minutes > 0)
                                <span class="text-green-600 font-medium">{{ $record->overtime_minutes }} د</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('attendance.edit', $record) }}"
                               class="text-teal-600 hover:text-teal-800 transition" title="تعديل">
                                <i class="fa fa-pen text-sm"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $records->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
