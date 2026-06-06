{{-- المسار: resources/views/invoices/aging.blade.php --}}
@extends('layouts.app')

@section('title', 'تقرير المستحقات')

@section('content')
<div class="space-y-6" dir="rtl">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">تقرير المستحقات</h1>
            <p class="text-sm text-gray-500 mt-1">العملاء الذين لديهم أرصدة مستحقة</p>
        </div>
        <a href="{{ route('invoices.index') }}"
           class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
            <i class="fa fa-arrow-right"></i>
            العودة للفواتير
        </a>
    </div>

    {{-- بطاقات الملخص --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-red-100">
            <p class="text-xs text-gray-500 mb-1">إجمالي المستحقات</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($summary['total_debt'], 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">عدد العملاء المدينين</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['total_debtors'] }}</p>
            <p class="text-xs text-gray-400 mt-1">عميل</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-orange-100">
            <p class="text-xs text-gray-500 mb-1">تجاوزوا حد الائتمان</p>
            <p class="text-2xl font-bold text-orange-600">{{ $summary['over_credit'] }}</p>
            <p class="text-xs text-gray-400 mt-1">عميل</p>
        </div>
    </div>

    {{-- جدول العملاء --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">قائمة العملاء المدينين</h2>
            @if($summary['total_debtors'] > 0)
            <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-medium">
                {{ $summary['total_debtors'] }} عميل
            </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">العميل</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الهاتف</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">التصنيف</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">فواتير مفتوحة</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">حد الائتمان</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">الرصيد المستحق</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($debtors as $customer)
                    @php
                        $overCredit = $customer->credit_limit > 0 && $customer->balance > $customer->credit_limit;
                        $rowClass   = $overCredit ? 'bg-red-50' : '';
                        $badgeColor = match($customer->classification) {
                            'vip'      => 'bg-yellow-100 text-yellow-700',
                            'inactive' => 'bg-gray-100 text-gray-500',
                            default    => 'bg-blue-100 text-blue-700',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $rowClass }}">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $customer->name }}</p>
                            @if($customer->company_name)
                            <p class="text-xs text-gray-400">{{ $customer->company_name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                {{ $customer->classification_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700">
                            {{ $customer->invoices->count() }}
                        </td>
                        <td class="px-4 py-3 text-left text-gray-500 text-xs">
                            @if($customer->credit_limit > 0)
                                {{ number_format($customer->credit_limit, 2) }} ج.س
                                @if($overCredit)
                                <span class="text-red-500 font-bold mr-1">⚠</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-left font-bold text-red-600">
                            {{ number_format($customer->balance, 2) }} ج.س
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('customers.statement', $customer->id) }}"
                               class="text-primary hover:underline text-xs ml-2">كشف حساب</a>
                            <a href="{{ route('customers.show', $customer->id) }}"
                               class="text-gray-500 hover:underline text-xs">عرض</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <i class="fa fa-check-circle text-4xl text-green-400 mb-3 block"></i>
                            <p class="text-gray-400 font-medium">لا توجد مستحقات</p>
                            <p class="text-gray-300 text-xs mt-1">جميع العملاء سددوا مستحقاتهم</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($summary['total_debtors'] > 0)
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="5" class="px-4 py-3 font-semibold text-gray-700 text-right">الإجمالي</td>
                        <td class="px-4 py-3 text-left font-bold text-red-600">
                            {{ number_format($summary['total_debt'], 2) }} ج.س
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
