{{-- المسار: resources/views/reports/hr/payroll-summary.blade.php --}}

@extends('layouts.app')

@section('title', __('reports.payroll_summary'))

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('reports.payroll_summary') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('reports.payroll_summary_desc') }}</p>
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
                <select name="month" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($data['month'] == $m)>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">السنة</label>
                <select name="year" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
                    @foreach(range(now()->year - 3, now()->year) as $y)
                        <option value="{{ $y }}" @selected($data['year'] == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700 transition w-full">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 inline-block ml-1"/>
                    عرض التقرير
                </button>
            </div>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 col-span-2 md:col-span-1">
            <p class="text-xs text-gray-500 mb-1">إجمالي الراتب الأساسي</p>
            <p class="text-lg font-bold text-gray-800">{{ number_format($data['summary']['total_basic'], 2) }}</p>
            <p class="text-xs text-gray-400">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي البدلات</p>
            <p class="text-lg font-bold text-green-700">{{ number_format($data['summary']['total_allowances'], 2) }}</p>
            <p class="text-xs text-gray-400">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الخصومات</p>
            <p class="text-lg font-bold text-red-600">{{ number_format($data['summary']['total_deductions'], 2) }}</p>
            <p class="text-xs text-gray-400">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-purple-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الصافي</p>
            <p class="text-lg font-bold text-purple-700">{{ number_format($data['summary']['total_net'], 2) }}</p>
            <p class="text-xs text-gray-400">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-emerald-200 p-4">
            <p class="text-xs text-gray-500 mb-1">تم الدفع</p>
            <p class="text-lg font-bold text-emerald-700">{{ $data['summary']['paid_count'] }}</p>
            <p class="text-xs text-gray-400">موظف</p>
        </div>
        <div class="bg-white rounded-xl border border-yellow-200 p-4">
            <p class="text-xs text-gray-500 mb-1">معلّق</p>
            <p class="text-lg font-bold text-yellow-600">{{ $data['summary']['pending_count'] }}</p>
            <p class="text-xs text-gray-400">موظف</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">
                تفاصيل الرواتب — {{ \Carbon\Carbon::create($data['year'], $data['month'])->translatedFormat('F Y') }}
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الموظف</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">القسم</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">الراتب الأساسي</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">البدلات</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">الخصومات</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">الصافي</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['rows'] as $payroll)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $payroll->employee->full_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $payroll->employee->department->name_ar ?? '-' }}</td>
                        <td class="px-4 py-3 text-left font-mono text-sm text-gray-700">{{ number_format($payroll->basic_salary, 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-sm text-green-600">{{ number_format($payroll->total_allowances, 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-sm text-red-500">{{ number_format($payroll->total_deductions, 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-sm font-bold text-purple-700">{{ number_format($payroll->net_salary, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($payroll->status === 'paid')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">مدفوع</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">معلّق</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">لا توجد بيانات رواتب لهذا الشهر</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr class="font-semibold">
                        <td colspan="2" class="px-4 py-3 text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 text-left font-mono">{{ number_format($data['summary']['total_basic'], 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-green-600">{{ number_format($data['summary']['total_allowances'], 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-red-500">{{ number_format($data['summary']['total_deductions'], 2) }}</td>
                        <td class="px-4 py-3 text-left font-mono text-purple-700">{{ number_format($data['summary']['total_net'], 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Export --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('reports.export-pdf', 'payroll-summary') }}?month={{ $data['month'] }}&year={{ $data['year'] }}"
           class="flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4"/> تصدير PDF
        </a>
    </div>

</div>
@endsection
