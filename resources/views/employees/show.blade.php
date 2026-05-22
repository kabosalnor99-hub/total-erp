{{-- المسار الكامل: resources/views/employees/show.blade.php --}}

@extends('layouts.app')

@section('title', 'ملف الموظف')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('employees.index') }}" class="text-gray-400 hover:text-teal-600 transition">
            <i class="fa fa-arrow-right text-lg"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">ملف الموظف</h1>
        <div class="mr-auto flex gap-2">
            @canPermission('hr.edit')
            <a href="{{ route('employees.edit', $employee) }}"
               class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition">
                <i class="fa fa-pen"></i> تعديل
            </a>
            @endcanPermission
        </div>
    </div>

    {{-- Profile Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-6 py-8">
            <div class="flex items-center gap-5">
                <img src="{{ $employee->photo_url }}" alt="{{ $employee->name }}"
                     class="w-20 h-20 rounded-full object-cover border-4 border-white/30">
                <div>
                    <h2 class="text-2xl font-bold text-white">{{ $employee->name }}</h2>
                    <p class="text-teal-100 text-sm mt-1">{{ $employee->job_title }}</p>
                    <p class="text-teal-200 text-xs mt-1">{{ $employee->employee_number }}</p>
                </div>
                <div class="mr-auto text-left">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        {{ $employee->status === 'active'     ? 'bg-green-100 text-green-700' : '' }}
                        {{ $employee->status === 'on_leave'   ? 'bg-blue-100 text-blue-700'  : '' }}
                        {{ $employee->status === 'terminated' ? 'bg-red-100 text-red-600'    : '' }}
                    ">
                        {{ $employee->status_label }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-gray-400 mb-1">القسم</p>
                <p class="font-medium text-gray-700">{{ $employee->department?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">نوع العقد</p>
                <p class="font-medium text-gray-700">{{ $employee->contract_type_label }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">تاريخ التعيين</p>
                <p class="font-medium text-gray-700">{{ $employee->hire_date->format('Y/m/d') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">سنوات الخدمة</p>
                <p class="font-medium text-teal-600">{{ $employee->years_of_service }} سنة</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Personal Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa fa-user text-teal-500"></i> البيانات الشخصية
                </h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><p class="text-gray-400">الهاتف</p><p class="font-medium">{{ $employee->phone ?? '—' }}</p></div>
                    <div><p class="text-gray-400">البريد</p><p class="font-medium">{{ $employee->email ?? '—' }}</p></div>
                    <div><p class="text-gray-400">الجنسية</p><p class="font-medium">{{ $employee->nationality }}</p></div>
                    <div><p class="text-gray-400">الجنس</p><p class="font-medium">{{ $employee->gender === 'male' ? 'ذكر' : 'أنثى' }}</p></div>
                    <div><p class="text-gray-400">تاريخ الميلاد</p><p class="font-medium">{{ $employee->date_of_birth?->format('Y/m/d') ?? '—' }}</p></div>
                    <div><p class="text-gray-400">الرقم الوطني</p><p class="font-medium">{{ $employee->national_id ?? '—' }}</p></div>
                    <div class="col-span-2"><p class="text-gray-400">العنوان</p><p class="font-medium">{{ $employee->address ?? '—' }}</p></div>
                </div>
            </div>

            {{-- Salary Structure --}}
            @if($employee->salaryStructure)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fa fa-money-bill text-teal-500"></i> هيكل الراتب الحالي
                    </h3>
                    <button onclick="document.getElementById('salary-modal').classList.remove('hidden')"
                            class="text-xs text-teal-600 hover:underline">تعديل الهيكل</button>
                </div>
                @php $s = $employee->salaryStructure; @endphp
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-teal-50 rounded-lg p-3">
                        <p class="text-gray-400 text-xs">الراتب الأساسي</p>
                        <p class="font-bold text-teal-700">{{ number_format($s->basic_salary) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-gray-400 text-xs">إجمالي البدلات</p>
                        <p class="font-bold text-green-700">{{ number_format($s->total_allowances) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3">
                        <p class="text-gray-400 text-xs">إجمالي الخصومات</p>
                        <p class="font-bold text-red-600">{{ number_format($s->total_deductions) }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-gray-400 text-xs">صافي الراتب</p>
                        <p class="font-bold text-blue-700">{{ number_format($s->net_salary) }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Latest Payrolls --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fa fa-file-invoice-dollar text-teal-500"></i> آخر الرواتب
                    </h3>
                    <a href="{{ route('payroll.index', ['employee_id' => $employee->id]) }}" class="text-xs text-teal-600 hover:underline">عرض الكل</a>
                </div>
                @forelse($employee->payrolls as $payroll)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm text-gray-600">{{ $payroll->month_name }}</span>
                    <span class="text-sm font-medium">{{ number_format($payroll->net_salary) }}</span>
                    <span class="px-2 py-0.5 rounded text-xs {{ $payroll->status_color }}">{{ $payroll->status_label }}</span>
                    <a href="{{ route('payroll.show', $payroll) }}" class="text-xs text-teal-600 hover:underline">عرض</a>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">لا توجد رواتب مسجلة</p>
                @endforelse
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">

            {{-- Leave Balance --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa fa-calendar-days text-teal-500"></i> أرصدة الإجازات
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">إجازة سنوية</span>
                        <span class="font-bold text-teal-600">{{ $employee->annual_leave_balance }} يوم</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">إجازة مرضية</span>
                        <span class="font-bold text-blue-600">{{ $employee->sick_leave_balance }} يوم</span>
                    </div>
                    @if($pendingLeaves > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-yellow-600">طلبات معلقة</span>
                        <span class="font-bold text-yellow-600">{{ $pendingLeaves }}</span>
                    </div>
                    @endif
                </div>
                <a href="{{ route('leaves.create', ['employee_id' => $employee->id]) }}"
                   class="mt-4 w-full block text-center bg-teal-50 hover:bg-teal-100 text-teal-700 px-3 py-2 rounded-lg text-xs font-medium transition">
                    <i class="fa fa-plus me-1"></i> طلب إجازة جديدة
                </a>
            </div>

            {{-- Attendance This Month --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa fa-clock text-teal-500"></i> حضور هذا الشهر
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">حاضر</span>
                        <span class="font-bold text-green-600">{{ $attendanceStats['present'] }} يوم</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">غائب</span>
                        <span class="font-bold text-red-500">{{ $attendanceStats['absent'] }} يوم</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">متأخر</span>
                        <span class="font-bold text-yellow-500">{{ $attendanceStats['late'] }} يوم</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">في إجازة</span>
                        <span class="font-bold text-blue-500">{{ $attendanceStats['on_leave'] }} يوم</span>
                    </div>
                </div>
            </div>

            {{-- Gratuity --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fa fa-award text-teal-500"></i> مكافأة نهاية الخدمة
                </h3>
                <p class="text-2xl font-bold text-teal-700">{{ number_format($gratuity) }}</p>
                <p class="text-xs text-gray-400 mt-1">محسوبة حتى اليوم</p>
            </div>

        </div>
    </div>
</div>

{{-- Salary Update Modal --}}
<div id="salary-modal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-800">تحديث هيكل الراتب</h3>
            <button onclick="document.getElementById('salary-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fa fa-times text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('employees.update-salary', $employee) }}">
            @csrf @method('POST')
            <div class="grid grid-cols-2 gap-3 text-sm">
                @php $s = $employee->salaryStructure; @endphp
                <div class="col-span-2">
                    <label class="block text-gray-600 mb-1">الراتب الأساسي</label>
                    <input type="number" name="basic_salary" value="{{ $s?->basic_salary ?? $employee->basic_salary }}" step="0.01" min="0"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">بدل السكن</label>
                    <input type="number" name="housing_allowance" value="{{ $s?->housing_allowance ?? 0 }}" step="0.01" min="0"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">بدل المواصلات</label>
                    <input type="number" name="transport_allowance" value="{{ $s?->transport_allowance ?? 0 }}" step="0.01" min="0"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">بدل الغذاء</label>
                    <input type="number" name="food_allowance" value="{{ $s?->food_allowance ?? 0 }}" step="0.01" min="0"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">التأمين الاجتماعي</label>
                    <input type="number" name="social_insurance" value="{{ $s?->social_insurance ?? 0 }}" step="0.01" min="0"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500">
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">نافذ من تاريخ</label>
                    <input type="date" name="effective_from" value="{{ now()->toDateString() }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('salary-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 rounded-lg text-sm">إلغاء</button>
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700">
                    حفظ التعديل
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
