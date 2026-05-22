{{-- المسار الكامل: resources/views/payroll/generate.blade.php --}}

@extends('layouts.app')

@section('title', 'توليد رواتب الشهر')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('payroll.index') }}" class="text-gray-400 hover:text-teal-600 transition">
            <i class="fa fa-arrow-right text-lg"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">توليد رواتب الشهر</h1>
            <p class="text-sm text-gray-500">اختر الشهر والموظفين ثم اضغط توليد</p>
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

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        <i class="fa fa-circle-xmark me-2"></i>{{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('payroll.process-generate') }}" x-data="payrollGenerate()" class="space-y-6">
        @csrf

        {{-- الشهر والسنة --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                <i class="fa fa-calendar text-teal-500 me-2"></i> الفترة المالية
            </h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الشهر <span class="text-red-500">*</span></label>
                    <select name="month" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500" required>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('ar')->monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">السنة <span class="text-red-500">*</span></label>
                    <select name="year" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500" required>
                        @foreach(range(now()->year, now()->year - 3) as $y)
                            <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- اختيار الموظفين --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-700">
                    <i class="fa fa-users text-teal-500 me-2"></i> الموظفون
                    <span class="text-sm font-normal text-gray-400">({{ $activeEmployees->count() }} موظف نشط)</span>
                </h2>
                <div class="flex gap-2">
                    {{-- فلتر القسم --}}
                    <select @change="filterDept($event.target.value)"
                            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-teal-500">
                        <option value="">كل الأقسام</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" @click="selectAll()"
                            class="px-3 py-1.5 bg-teal-50 text-teal-700 rounded-lg text-sm hover:bg-teal-100 transition">
                        تحديد الكل
                    </button>
                    <button type="button" @click="deselectAll()"
                            class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">
                        إلغاء الكل
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-right">
                            <th class="px-3 py-2 font-medium text-gray-600 w-10">
                                <input type="checkbox" @change="toggleAll($event.target.checked)"
                                       class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            </th>
                            <th class="px-3 py-2 font-medium text-gray-600">الموظف</th>
                            <th class="px-3 py-2 font-medium text-gray-600">القسم</th>
                            <th class="px-3 py-2 font-medium text-gray-600">المسمى الوظيفي</th>
                            <th class="px-3 py-2 font-medium text-gray-600">الراتب الأساسي</th>
                            <th class="px-3 py-2 font-medium text-gray-600">الإجمالي المتوقع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" x-ref="tbody">
                        @foreach($activeEmployees as $emp)
                        <tr class="hover:bg-gray-50 transition employee-row" data-dept="{{ $emp->department_id }}">
                            <td class="px-3 py-2">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                                       x-model="selected" @change="updateCount()"
                                       class="rounded border-gray-300 text-teal-600 focus:ring-teal-500" checked>
                            </td>
                            <td class="px-3 py-2">
                                <div class="font-medium text-gray-800">{{ $emp->name }}</div>
                                <div class="text-xs text-gray-400">{{ $emp->employee_number }}</div>
                            </td>
                            <td class="px-3 py-2 text-gray-600">{{ $emp->department?->name ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $emp->job_title }}</td>
                            <td class="px-3 py-2 font-medium text-gray-800">{{ number_format($emp->basic_salary, 2) }}</td>
                            <td class="px-3 py-2 font-semibold text-teal-700">
                                @php
                                    $struct = $emp->salaryStructure;
                                    $gross = $struct
                                        ? $struct->basic_salary + $struct->housing_allowance + $struct->transport_allowance + $struct->food_allowance + $struct->other_allowances
                                        : $emp->basic_salary;
                                @endphp
                                {{ number_format($gross, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 text-sm text-gray-500">
                تم تحديد <span x-text="selectedCount" class="font-semibold text-teal-600">{{ $activeEmployees->count() }}</span> موظف
            </div>
        </div>

        {{-- ملاحظة --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700">
            <i class="fa fa-circle-info me-2"></i>
            سيتم حساب الرواتب بناءً على سجلات الحضور والغياب لهذا الشهر تلقائياً. يمكنك تعديل أي راتب يدوياً بعد التوليد.
        </div>

        {{-- Buttons --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('payroll.index') }}"
               class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                إلغاء
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition font-medium">
                <i class="fa fa-cogs me-1"></i> توليد الرواتب
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function payrollGenerate() {
    return {
        selected: Array.from({length: {{ $activeEmployees->count() }}}, (_, i) => {{ $activeEmployees->pluck('id') }}[i]),
        selectedCount: {{ $activeEmployees->count() }},

        selectAll() {
            document.querySelectorAll('input[name="employee_ids[]"]').forEach(cb => cb.checked = true);
            this.selectedCount = document.querySelectorAll('input[name="employee_ids[]"]').length;
        },
        deselectAll() {
            document.querySelectorAll('input[name="employee_ids[]"]').forEach(cb => cb.checked = false);
            this.selectedCount = 0;
        },
        toggleAll(checked) {
            document.querySelectorAll('input[name="employee_ids[]"]').forEach(cb => cb.checked = checked);
            this.updateCount();
        },
        updateCount() {
            this.selectedCount = document.querySelectorAll('input[name="employee_ids[]"]:checked').length;
        },
        filterDept(deptId) {
            document.querySelectorAll('.employee-row').forEach(row => {
                if (!deptId || row.dataset.dept == deptId) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    }
}
</script>
@endpush
@endsection
