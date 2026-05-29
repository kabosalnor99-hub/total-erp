@extends('layouts.app')

@section('title', 'الفواتير المتأخرة')

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">الفواتير المتأخرة</h1>
            <p class="text-sm text-gray-500 mt-1">الفواتير التي تجاوزت تاريخ الاستحقاق ولم يتم تسديدها بالكامل</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
            العودة للتقارير
        </a>
    </div>

    {{-- Summary Cards --}}
    @php
        $rows = collect($data['rows']);
        $totalDue = $rows->sum('due');
        $totalInvoices = $rows->count();
        $avgOverdueDays = $rows->avg('overdue_days');
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي المبالغ المتأخرة</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($totalDue, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">عدد الفواتير المتأخرة</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalInvoices }}</p>
            <p class="text-xs text-gray-400 mt-1">فاتورة</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-200 p-4">
            <p class="text-xs text-gray-500 mb-1">متوسط أيام التأخير</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($avgOverdueDays ?? 0, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">يوم</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">قائمة الفواتير المتأخرة</h2>
            @if($totalInvoices > 0)
            <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-medium">{{ $totalInvoices }} فاتورة متأخرة</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">رقم الفاتورة</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">العميل</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">تاريخ الاستحقاق</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">أيام التأخير</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">إجمالي الفاتورة</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">المدفوع</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">المتبقي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['rows'] as $row)
                    @php
                        $urgencyClass = match(true) {
                            $row['overdue_days'] >= 90 => 'bg-red-50',
                            $row['overdue_days'] >= 30 => 'bg-orange-50',
                            default => ''
                        };
                        $badgeClass = match(true) {
                            $row['overdue_days'] >= 90 => 'bg-red-100 text-red-700',
                            $row['overdue_days'] >= 30 => 'bg-orange-100 text-orange-700',
                            default => 'bg-yellow-100 text-yellow-700'
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $urgencyClass }}">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $row['number'] }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $row['customer'] }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $row['due_date'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ $row['overdue_days'] }} يوم
                            </span>
                        </td>
                        <td class="px-4 py-3 text-left text-gray-700">{{ number_format($row['total'], 2) }} ج.س</td>
                        <td class="px-4 py-3 text-left text-green-600">{{ number_format($row['paid'], 2) }} ج.س</td>
                        <td class="px-4 py-3 text-left font-bold text-red-600">{{ number_format($row['due'], 2) }} ج.س</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center">
                            <x-heroicon-o-check-circle class="w-10 h-10 text-green-400 mx-auto mb-2"/>
                            <p class="text-gray-400">لا توجد فواتير متأخرة</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($data['rows']) > 0)
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="4" class="px-4 py-3 font-semibold text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 text-left font-bold text-gray-700">{{ number_format(collect($data['rows'])->sum('total'), 2) }} ج.س</td>
                        <td class="px-4 py-3 text-left font-bold text-green-600">{{ number_format(collect($data['rows'])->sum('paid'), 2) }} ج.س</td>
                        <td class="px-4 py-3 text-left font-bold text-red-600">{{ number_format($totalDue, 2) }} ج.س</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Legend --}}
    <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-yellow-200 inline-block"></span> أقل من 30 يوم</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-orange-200 inline-block"></span> 30–90 يوم</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-200 inline-block"></span> أكثر من 90 يوم</span>
    </div>

</div>
@endsection
