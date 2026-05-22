{{-- المسار الكامل: resources/views/attendance/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'تعديل سجل الحضور')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('attendance.show', $attendance) }}" class="text-gray-400 hover:text-teal-600 transition">
            <i class="fa fa-arrow-right text-lg"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">تعديل سجل الحضور</h1>
            <p class="text-sm text-gray-500">{{ $attendance->employee->name }} — {{ $attendance->date->format('Y/m/d') }}</p>
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

    <form method="POST" action="{{ route('attendance.update', $attendance) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">

            {{-- بيانات الموظف (للعرض فقط) --}}
            <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold">
                    {{ mb_substr($attendance->employee->name, 0, 1) }}
                </div>
                <div>
                    <p class="font-semibold text-gray-800">{{ $attendance->employee->name }}</p>
                    <p class="text-xs text-gray-500">{{ $attendance->employee->employee_number }} — {{ $attendance->employee->job_title }}</p>
                </div>
            </div>

            {{-- الحالة --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-2" id="statusButtons">
                    @foreach(['present'=>['حاضر','green'],'absent'=>['غائب','red'],'late'=>['متأخر','yellow'],'on_leave'=>['في إجازة','blue'],'holiday'=>['إجازة رسمية','gray']] as $val => [$label, $color])
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="{{ $val }}" class="sr-only status-radio"
                               {{ old('status', $attendance->status) === $val ? 'checked' : '' }}>
                        <div class="px-3 py-2 rounded-lg border-2 text-sm text-center font-medium transition status-btn
                            {{ old('status', $attendance->status) === $val ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                            {{ $label }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- التاريخ --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">التاريخ</label>
                <input type="date" name="date" value="{{ old('date', $attendance->date->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>

            {{-- وقت الدخول والخروج --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">وقت الدخول</label>
                    <input type="time" name="check_in" value="{{ old('check_in', $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">وقت الخروج</label>
                    <input type="time" name="check_out" value="{{ old('check_out', $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                </div>
            </div>

            {{-- التأخير والإضافي --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">دقائق التأخير</label>
                    <input type="number" name="late_minutes" min="0"
                           value="{{ old('late_minutes', $attendance->late_minutes) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
                           placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">دقائق العمل الإضافي</label>
                    <input type="number" name="overtime_minutes" min="0"
                           value="{{ old('overtime_minutes', $attendance->overtime_minutes) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
                           placeholder="0">
                </div>
            </div>

            {{-- ملاحظات --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none resize-none"
                          placeholder="أي ملاحظات إضافية...">{{ old('notes', $attendance->notes) }}</textarea>
            </div>

        </div>

        {{-- أزرار الحفظ --}}
        <div class="flex justify-end gap-3 mt-4">
            <a href="{{ route('attendance.show', $attendance) }}"
               class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">
                إلغاء
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-teal-600 text-white rounded-lg text-sm font-medium hover:bg-teal-700 transition">
                <i class="fa fa-save me-1"></i> حفظ التعديلات
            </button>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.status-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.status-btn').forEach(btn => {
            btn.classList.remove('border-teal-500','bg-teal-50','text-teal-700');
            btn.classList.add('border-gray-200','text-gray-600');
        });
        this.nextElementSibling.classList.remove('border-gray-200','text-gray-600');
        this.nextElementSibling.classList.add('border-teal-500','bg-teal-50','text-teal-700');
    });
});
</script>
@endpush
