{{-- المسار الكامل: resources/views/attendance/create.blade.php --}}

@extends('layouts.app')

@section('title', 'تسجيل الحضور')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('attendance.index') }}" class="text-gray-400 hover:text-teal-600 transition">
            <i class="fa fa-arrow-right text-lg"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">تسجيل الحضور</h1>
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($today)->locale('ar')->isoFormat('dddd، D MMMM YYYY') }}</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <ul class="list-disc list-inside space-y-1 text-sm text-red-600">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
        <i class="fa fa-circle-check me-2"></i>{{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('attendance.store') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">

            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-700">
                    <i class="fa fa-users text-teal-500 me-2"></i>
                    تسجيل حضور الموظفين
                    @if($pending->count() > 0)
                        <span class="text-sm font-normal text-gray-400">({{ $pending->count() }} موظف لم يُسجَّل)</span>
                    @else
                        <span class="text-sm font-normal text-green-500">(تم تسجيل جميع الموظفين اليوم)</span>
                    @endif
                </h2>
                <div>
                    <input type="date" name="date" value="{{ $today }}"
                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
            </div>

            {{-- تعيين الكل --}}
            <div class="flex gap-2 flex-wrap">
                <button type="button" onclick="setAll('present')"
                        class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-sm hover:bg-green-200 transition">
                    <i class="fa fa-check me-1"></i> الكل حاضر
                </button>
                <button type="button" onclick="setAll('absent')"
                        class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200 transition">
                    <i class="fa fa-xmark me-1"></i> الكل غائب
                </button>
                <button type="button" onclick="setAll('holiday')"
                        class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                    <i class="fa fa-umbrella me-1"></i> عطلة رسمية
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-right border-b border-gray-200">
                            <th class="px-3 py-2 font-medium text-gray-600">الموظف</th>
                            <th class="px-3 py-2 font-medium text-gray-600">الحالة</th>
                            <th class="px-3 py-2 font-medium text-gray-600">وقت الحضور</th>
                            <th class="px-3 py-2 font-medium text-gray-600">وقت الانصراف</th>
                            <th class="px-3 py-2 font-medium text-gray-600">تأخير (دقيقة)</th>
                            <th class="px-3 py-2 font-medium text-gray-600">أوفرتايم (دقيقة)</th>
                            <th class="px-3 py-2 font-medium text-gray-600">ملاحظة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pending as $index => $emp)
                        <tr class="hover:bg-gray-50">
                            <input type="hidden" name="records[{{ $index }}][employee_id]" value="{{ $emp->id }}">
                            <td class="px-3 py-2">
                                <div class="font-medium text-gray-800">{{ $emp->name }}</div>
                                <div class="text-xs text-gray-400">{{ $emp->department?->name ?? '—' }}</div>
                            </td>
                            <td class="px-3 py-2">
                                <select name="records[{{ $index }}][status]" class="status-select border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 w-28">
                                    <option value="present">حاضر</option>
                                    <option value="absent">غائب</option>
                                    <option value="late">متأخر</option>
                                    <option value="on_leave">إجازة</option>
                                    <option value="holiday">عطلة</option>
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="time" name="records[{{ $index }}][check_in]" value="08:00"
                                       class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 w-24" dir="ltr">
                            </td>
                            <td class="px-3 py-2">
                                <input type="time" name="records[{{ $index }}][check_out]"
                                       class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 w-24" dir="ltr">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="records[{{ $index }}][late_minutes]" value="0" min="0"
                                       class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 w-20">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="records[{{ $index }}][overtime_minutes]" value="0" min="0"
                                       class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 w-20">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="records[{{ $index }}][notes]"
                                       class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-teal-500 w-28"
                                       placeholder="ملاحظة">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pending->isEmpty())
            <div class="text-center py-6 text-gray-400">
                <i class="fa fa-circle-check text-4xl text-green-400 mb-2"></i>
                <p>تم تسجيل حضور جميع الموظفين لهذا اليوم</p>
            </div>
            @endif
        </div>

        @if($pending->isNotEmpty())
        <div class="flex justify-end gap-3 mt-4">
            <a href="{{ route('attendance.index') }}"
               class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                إلغاء
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition font-medium">
                <i class="fa fa-save me-1"></i> حفظ سجلات الحضور
            </button>
        </div>
        @endif
    </form>
</div>

@push('scripts')
<script>
function setAll(status) {
    document.querySelectorAll('.status-select').forEach(select => {
        select.value = status;
    });
}
</script>
@endpush
@endsection
