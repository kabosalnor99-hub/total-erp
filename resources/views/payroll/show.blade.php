{{-- المسار الكامل: resources/views/payroll/show.blade.php --}}

@extends('layouts.app')

@section('title', 'تفاصيل الراتب')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('payroll.index', ['month' => $payroll->month, 'year' => $payroll->year]) }}"
               class="text-gray-400 hover:text-teal-600 transition">
                <i class="fa fa-arrow-right text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">تفاصيل الراتب</h1>
                <p class="text-sm text-gray-500">
                    {{ $payroll->employee->name }} —
                    {{ \Carbon\Carbon::create()->month($payroll->month)->locale('ar')->monthName }}
                    {{ $payroll->year }}
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('payroll.payslip', $payroll) }}"
               class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
                <i class="fa fa-file-pdf me-1"></i> طباعة قسيمة
            </a>
            @if($payroll->status === 'draft')
            <form method="POST" action="{{ route('payroll.approve', $payroll) }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
                    <i class="fa fa-check me-1"></i> اعتماد
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
        <i class="fa fa-circle-check me-2"></i>{{ session('success') }}
    </div>
    @endif

    {{-- بيانات الموظف --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-4">
            <img src="{{ $payroll->employee->photo_url }}" alt="صورة الموظف"
                 class="w-16 h-16 rounded-full object-cover border-2 border-teal-200">
            <div class="flex-1">
                <h2 class="text-lg font-bold text-gray-800">{{ $payroll->employee->name }}</h2>
                <p class="text-sm text-gray-500">{{ $payroll->employee->job_title }} — {{ $payroll->employee->department?->name ?? '—' }}</p>
                <p class="text-sm text-gray-400">{{ $payroll->employee->employee_number }}</p>
            </div>
            <div class="text-left">
                @if($payroll->status === 'draft')
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">مسودة</span>
                @elseif($payroll->status === 'approved')
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">معتمد</span>
                @else
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">مدفوع</span>
                @endif
            </div>
        </div>
    </div>

    {{-- تفاصيل الراتب --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- الإيرادات --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                <i class="fa fa-plus-circle text-green-500 me-2"></i> الإيرادات
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">الراتب الأساسي</span>
                    <span class="font-medium">{{ number_format($payroll->basic_salary, 2) }}</span>
                </div>
                @if($payroll->housing_allowance > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">بدل السكن</span>
                    <span class="font-medium">{{ number_format($payroll->housing_allowance, 2) }}</span>
                </div>
                @endif
                @if($payroll->transport_allowance > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">بدل المواصلات</span>
                    <span class="font-medium">{{ number_format($payroll->transport_allowance, 2) }}</span>
                </div>
                @endif
                @if($payroll->food_allowance > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">بدل الغذاء</span>
                    <span class="font-medium">{{ number_format($payroll->food_allowance, 2) }}</span>
                </div>
                @endif
                @if($payroll->other_allowances > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">بدلات أخرى</span>
                    <span class="font-medium">{{ number_format($payroll->other_allowances, 2) }}</span>
                </div>
                @endif
                @if($payroll->overtime_amount > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">أوفرتايم ({{ $payroll->overtime_hours }} ساعة)</span>
                    <span class="font-medium text-green-600">{{ number_format($payroll->overtime_amount, 2) }}</span>
                </div>
                @endif
                @if($payroll->bonus > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">مكافأة</span>
                    <span class="font-medium text-green-600">{{ number_format($payroll->bonus, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm font-bold border-t border-gray-100 pt-3">
                    <span class="text-gray-800">إجمالي الراتب</span>
                    <span class="text-teal-700">{{ number_format($payroll->gross_salary, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- الخصومات --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                <i class="fa fa-minus-circle text-red-500 me-2"></i> الخصومات
            </h3>
            <div class="space-y-3">
                @if($payroll->absence_deduction > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">خصم الغياب ({{ $payroll->absent_days }} يوم)</span>
                    <span class="font-medium text-red-500">-{{ number_format($payroll->absence_deduction, 2) }}</span>
                </div>
                @endif
                @if($payroll->late_deduction > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">خصم التأخير ({{ $payroll->late_minutes }} دقيقة)</span>
                    <span class="font-medium text-red-500">-{{ number_format($payroll->late_deduction, 2) }}</span>
                </div>
                @endif
                @if($payroll->social_insurance > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">التأمين الاجتماعي</span>
                    <span class="font-medium text-red-500">-{{ number_format($payroll->social_insurance, 2) }}</span>
                </div>
                @endif
                @if($payroll->tax_deduction > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">ضريبة الدخل</span>
                    <span class="font-medium text-red-500">-{{ number_format($payroll->tax_deduction, 2) }}</span>
                </div>
                @endif
                @if($payroll->advance_deduction > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">خصم سلفة</span>
                    <span class="font-medium text-red-500">-{{ number_format($payroll->advance_deduction, 2) }}</span>
                </div>
                @endif
                @if($payroll->other_deductions > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">خصومات أخرى</span>
                    <span class="font-medium text-red-500">-{{ number_format($payroll->other_deductions, 2) }}</span>
                </div>
                @endif
                @if($payroll->total_deductions == 0)
                <p class="text-sm text-gray-400 text-center py-2">لا توجد خصومات</p>
                @endif
                <div class="flex justify-between text-sm font-bold border-t border-gray-100 pt-3">
                    <span class="text-gray-800">إجمالي الخصومات</span>
                    <span class="text-red-600">-{{ number_format($payroll->total_deductions, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- صافي الراتب --}}
    <div class="bg-teal-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-teal-100 text-sm">صافي الراتب المستحق</p>
                <p class="text-4xl font-bold mt-1">{{ number_format($payroll->net_salary, 2) }}</p>
                <p class="text-teal-200 text-sm mt-1">
                    {{ \Carbon\Carbon::create()->month($payroll->month)->locale('ar')->monthName }}
                    {{ $payroll->year }}
                </p>
            </div>
            <div class="text-left">
                <p class="text-teal-100 text-sm">أيام الحضور</p>
                <p class="text-2xl font-bold">{{ $payroll->working_days }}</p>
            </div>
        </div>
        @if($payroll->status === 'paid')
        <div class="mt-4 pt-4 border-t border-teal-500 text-sm text-teal-100">
            <i class="fa fa-check-circle me-2"></i>
            تم الدفع بتاريخ {{ $payroll->payment_date?->format('Y-m-d') }} —
            {{ $payroll->payment_method === 'cash' ? 'نقدي' : ($payroll->payment_method === 'bank_transfer' ? 'تحويل بنكي' : 'شيك') }}
        </div>
        @endif
    </div>

    {{-- تعديل يدوي (مسودة فقط) --}}
    @if($payroll->status === 'draft')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
            <i class="fa fa-pen text-teal-500 me-2"></i> تعديل يدوي
        </h3>
        <form method="POST" action="{{ route('payroll.update', $payroll) }}" class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">مكافأة</label>
                <input type="number" name="bonus" value="{{ $payroll->bonus }}" step="0.01" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">أوفرتايم</label>
                <input type="number" name="overtime_amount" value="{{ $payroll->overtime_amount }}" step="0.01" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">خصم سلفة</label>
                <input type="number" name="advance_deduction" value="{{ $payroll->advance_deduction }}" step="0.01" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">خصومات أخرى</label>
                <input type="number" name="other_deductions" value="{{ $payroll->other_deductions }}" step="0.01" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">ملاحظات</label>
                <input type="text" name="notes" value="{{ $payroll->notes }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit"
                        class="px-5 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition">
                    <i class="fa fa-calculator me-1"></i> حفظ وإعادة الحساب
                </button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
