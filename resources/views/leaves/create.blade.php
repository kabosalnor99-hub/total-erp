{{-- المسار الكامل: resources/views/leaves/create.blade.php --}}

@extends('layouts.app')

@section('title', 'طلب إجازة جديد')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('leaves.index') }}" class="text-gray-400 hover:text-teal-600 transition">
            <i class="fa fa-arrow-right text-lg"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">طلب إجازة جديد</h1>
            <p class="text-sm text-gray-500">تقديم طلب إجازة لموظف</p>
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

    <form method="POST" action="{{ route('leaves.store') }}" x-data="leaveForm()" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الموظف <span class="text-red-500">*</span></label>
                <select name="employee_id" @change="loadEmployee($event.target)" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                    <option value="">-- اختر الموظف --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}"
                                data-annual="{{ $emp->annual_leave_balance }}"
                                data-sick="{{ $emp->sick_leave_balance }}"
                                {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->employee_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- رصيد الإجازات --}}
            <div x-show="showBalance" x-cloak class="grid grid-cols-2 gap-3">
                <div class="bg-blue-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-blue-600">رصيد الإجازة السنوية</p>
                    <p class="text-xl font-bold text-blue-700" x-text="annualBalance + ' يوم'"></p>
                </div>
                <div class="bg-red-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-red-600">رصيد الإجازة المرضية</p>
                    <p class="text-xl font-bold text-red-700" x-text="sickBalance + ' يوم'"></p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">نوع الإجازة <span class="text-red-500">*</span></label>
                <select name="type" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                    <option value="annual"    {{ old('type') === 'annual'    ? 'selected' : '' }}>إجازة سنوية</option>
                    <option value="sick"      {{ old('type') === 'sick'      ? 'selected' : '' }}>إجازة مرضية</option>
                    <option value="emergency" {{ old('type') === 'emergency' ? 'selected' : '' }}>إجازة طارئة</option>
                    <option value="unpaid"    {{ old('type') === 'unpaid'    ? 'selected' : '' }}>إجازة بدون راتب</option>
                    <option value="other"     {{ old('type') === 'other'     ? 'selected' : '' }}>أخرى</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ البداية <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}"
                           @change="calcDays()" x-model="startDate" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ النهاية <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}"
                           @change="calcDays()" x-model="endDate" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                </div>
            </div>

            {{-- عدد الأيام --}}
            <div x-show="days > 0" x-cloak
                 class="bg-teal-50 border border-teal-200 rounded-lg p-3 text-center">
                <p class="text-sm text-teal-600">
                    مدة الإجازة: <strong class="text-teal-800 text-lg" x-text="days"></strong> يوم
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">سبب الإجازة</label>
                <textarea name="reason" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500"
                          placeholder="اذكر سبب الإجازة (اختياري)...">{{ old('reason') }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('leaves.index') }}"
               class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                إلغاء
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition font-medium">
                <i class="fa fa-paper-plane me-1"></i> تقديم الطلب
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function leaveForm() {
    return {
        startDate: '{{ old('start_date') }}',
        endDate: '{{ old('end_date') }}',
        days: 0,
        showBalance: false,
        annualBalance: 0,
        sickBalance: 0,

        loadEmployee(select) {
            const option = select.options[select.selectedIndex];
            if (option.value) {
                this.annualBalance = option.dataset.annual || 0;
                this.sickBalance   = option.dataset.sick   || 0;
                this.showBalance   = true;
            } else {
                this.showBalance = false;
            }
        },

        calcDays() {
            if (this.startDate && this.endDate) {
                const s = new Date(this.startDate);
                const e = new Date(this.endDate);
                if (e >= s) {
                    this.days = Math.round((e - s) / (1000 * 60 * 60 * 24)) + 1;
                } else {
                    this.days = 0;
                }
            }
        }
    }
}
</script>
@endpush
@endsection
