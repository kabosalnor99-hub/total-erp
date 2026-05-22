{{-- المسار الكامل: resources/views/payroll/report.blade.php --}}

@extends('layouts.app')

@section('title', 'تقرير الرواتب')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">تقرير الرواتب</h1>
            <p class="text-sm text-gray-500 mt-1">ملخص رواتب شهر
                @php
                    $months = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
                               7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
                @endphp
                {{ $months[$month] ?? $month }} {{ $year }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('payroll.index', ['month' => $month, 'year' => $year]) }}"
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">
                <i class="fa fa-arrow-right me-1"></i> رجوع للرواتب
            </a>
            <button onclick="window.print()"
                    class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition font-medium">
                <i class="fa fa-print me-1"></i> طباعة التقرير
            </button>
        </div>
    </div>

    {{-- فلتر الشهر --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('payroll.report') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">الشهر</label>
                <select name="month" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">السنة</label>
                <select name="year" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700 transition">
                <i class="fa fa-filter me-1"></i> عرض
            </button>
        </form>
    </div>

    @if($payrolls->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <i class="fa fa-file-invoice-dollar text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">لا توجد رواتب معتمدة لهذا الشهر</p>
        <a href="{{ route('payroll.index') }}" class="mt-3 inline-block text-teal-600 text-sm hover:underline">
            الذهاب لإدارة الرواتب
        </a>
    </div>
    @else

    {{-- بطاقات الملخص --}}
    @php
        $totalGross      = $payrolls->sum('gross_salary');
        $totalDeductions = $payrolls->sum('total_deductions');
        $totalNet        = $payrolls->sum('net_salary');
        $paidCount       = $payrolls->where('status', 'paid')->count();
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">عدد الموظفين</p>
            <p class="text-2xl font-bold text-gray-800">{{ $payrolls->count() }}</p>
            <p class="text-xs text-green-600 mt-1">{{ $paidCount }} مدفوع</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الرواتب</p>
            <p class="text-2xl font-bold text-teal-600">{{ number_format($totalGross, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">جنيه</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الخصومات</p>
            <p class="text-2xl font-bold text-red-500">{{ number_format($totalDeductions, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">جنيه</p>
        </div>
        <div class="bg-teal-600 rounded-xl p-4 text-white">
            <p class="text-xs opacity-80 mb-1">صافي المدفوعات</p>
            <p class="text-2xl font-bold">{{ number_format($totalNet, 0) }}</p>
            <p class="text-xs opacity-70 mt-1">جنيه</p>
        </div>
    </div>

    {{-- تقرير حسب القسم --}}
    @if($byDepartment->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800"><i class="fa fa-building text-teal-600 me-2"></i>ملخص حسب القسم</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-teal-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold">القسم</th>
                        <th class="px-4 py-3 text-center font-semibold">الموظفون</th>
                        <th class="px-4 py-3 text-left font-semibold">إجمالي الرواتب</th>
                        <th class="px-4 py-3 text-left font-semibold">إجمالي الخصومات</th>
                        <th class="px-4 py-3 text-left font-semibold">صافي الرواتب</th>
                        <th class="px-4 py-3 text-center font-semibold">النسبة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($byDepartment as $deptName => $data)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $deptName }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $data['count'] }}</td>
                        <td class="px-4 py-3 text-left font-mono text-gray-800">{{ number_format($data['gross'], 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-red-500">{{ number_format($data['deductions'], 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono font-semibold text-teal-600">{{ number_format($data['net'], 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $pct = $totalNet > 0 ? round($data['net'] / $totalNet * 100, 1) : 0; @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2">
                                    <div class="bg-teal-500 h-2 rounded-full" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td class="px-4 py-3 text-gray-800">الإجمالي</td>
                        <td class="px-4 py-3 text-center text-gray-800">{{ $payrolls->count() }}</td>
                        <td class="px-4 py-3 text-left font-mono text-gray-800">{{ number_format($totalGross, 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-red-500">{{ number_format($totalDeductions, 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-teal-600">{{ number_format($totalNet, 2) }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- تفاصيل كل موظف --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800"><i class="fa fa-users text-teal-600 me-2"></i>تفاصيل الرواتب الفردية</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold">الموظف</th>
                        <th class="px-4 py-3 text-right font-semibold">القسم</th>
                        <th class="px-4 py-3 text-center font-semibold">الحضور</th>
                        <th class="px-4 py-3 text-center font-semibold">الغياب</th>
                        <th class="px-4 py-3 text-left font-semibold">إجمالي</th>
                        <th class="px-4 py-3 text-left font-semibold">خصومات</th>
                        <th class="px-4 py-3 text-left font-semibold">صافي</th>
                        <th class="px-4 py-3 text-center font-semibold">الحالة</th>
                        <th class="px-4 py-3 text-center font-semibold">قسيمة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($payrolls as $payroll)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $payroll->employee->name }}</p>
                            <p class="text-xs text-gray-400">{{ $payroll->employee->employee_number }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $payroll->employee->department?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-green-600 font-semibold">{{ $payroll->working_days - $payroll->absent_days }}</span>
                            <span class="text-gray-400 text-xs">/{{ $payroll->working_days }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="{{ $payroll->absent_days > 0 ? 'text-red-500 font-semibold' : 'text-gray-400' }}">
                                {{ $payroll->absent_days }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-left font-mono text-gray-700">{{ number_format($payroll->gross_salary, 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-red-400">{{ number_format($payroll->total_deductions, 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono font-semibold text-teal-600">{{ number_format($payroll->net_salary, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $payroll->status_color }}">
                                {{ $payroll->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('payroll.pdf', $payroll) }}" target="_blank"
                               class="text-teal-600 hover:text-teal-800 text-xs" title="تحميل قسيمة الراتب">
                                <i class="fa fa-file-pdf text-base"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

@push('styles')
<style>
@media print {
    .no-print, nav, aside, button, a[href] { display: none !important; }
    body { background: white; }
    .bg-teal-600 { background: #00838F !important; -webkit-print-color-adjust: exact; }
}
</style>
@endpush
