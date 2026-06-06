{{-- المسار: resources/views/invoices/aging.blade.php --}}
@extends('layouts.app')

@section('title', 'تقرير المستحقات المتأخرة')

@section('content')
<div class="space-y-6" dir="rtl">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">تقرير المستحقات المتأخرة</h1>
            <p class="text-sm text-gray-500 mt-1">الفواتير التي تجاوزت تاريخ الاستحقاق ولم يتم سدادها بالكامل</p>
        </div>
        <a href="{{ route('invoices.index') }}"
           class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
            <i class="fa fa-arrow-right"></i>
            العودة للفواتير
        </a>
    </div>

    {{-- حساب الإحصائيات --}}
    @php
        $totalDue      = $overdue->sum("remaining_amount");
        $totalInvoices = $overdue->count();
        $avgDays       = $overdue->avg(fn($i) => now()->diffInDays($i->due_date));

        $buckets = [
            '1_30'  => $overdue->filter(fn($i) => now()->diffInDays($i->due_date) <= 30),
            '31_60' => $overdue->filter(fn($i) => now()->diffInDays($i->due_date) > 30 && now()->diffInDays($i->due_date) <= 60),
            '61_90' => $overdue->filter(fn($i) => now()->diffInDays($i->due_date) > 60 && now()->diffInDays($i->due_date) <= 90),
            '90p'   => $overdue->filter(fn($i) => now()->diffInDays($i->due_date) > 90),
        ];
    @endphp

    {{-- بطاقات الملخص --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-red-100">
            <p class="text-xs text-gray-500 mb-1">إجمالي المتأخرات</p>
            <p class="text-xl font-bold text-red-600">{{ number_format($totalDue, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">عدد الفواتير</p>
            <p class="text-xl font-bold text-gray-800">{{ $totalInvoices }}</p>
            <p class="text-xs text-gray-400 mt-1">فاتورة</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-orange-100">
            <p class="text-xs text-gray-500 mb-1">متوسط أيام التأخير</p>
            <p class="text-xl font-bold text-orange-600">{{ number_format($avgDays ?? 0, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">يوم</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-red-100">
            <p class="text-xs text-gray-500 mb-1">أكثر من 90 يوم</p>
            <p class="text-xl font-bold text-red-800">{{ $buckets['90p']->count() }}</p>
            <p class="text-xs text-gray-400 mt-1">فاتورة حرجة</p>
        </div>
    </div>

    {{-- شرائح التقادم --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach([
            ['label' => '1 – 30 يوم',   'key' => '1_30',  'color' => 'yellow'],
            ['label' => '31 – 60 يوم',  'key' => '31_60', 'color' => 'orange'],
            ['label' => '61 – 90 يوم',  'key' => '61_90', 'color' => 'red'],
            ['label' => 'أكثر من 90 يوم','key' => '90p',  'color' => 'red'],
        ] as $b)
        @php $cnt = $buckets[$b['key']]->count(); @endphp
        <div class="bg-white rounded-lg p-3 shadow-sm border border-{{ $b['color'] }}-100 text-center">
            <p class="text-xs text-gray-500 mb-1">{{ $b['label'] }}</p>
            <p class="text-2xl font-bold text-{{ $b['color'] }}-600">{{ $cnt }}</p>
            <p class="text-xs text-gray-400">{{ number_format($buckets[$b['key']]->sum('remaining_amount'), 2) }} ج.س</p>
        </div>
        @endforeach
    </div>

    {{-- جدول الفواتير --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">قائمة الفواتير المتأخرة</h2>
            @if($totalInvoices > 0)
            <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-medium">
                {{ $totalInvoices }} فاتورة متأخرة
            </span>
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
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($overdue as $invoice)
                    @php
                        $overdueDays = now()->diffInDays($invoice->due_date);
                        $remaining   = $invoice->remaining_amount;

                        $rowClass   = match(true) {
                            $overdueDays >= 90 => 'bg-red-50',
                            $overdueDays >= 60 => 'bg-orange-50',
                            $overdueDays >= 30 => 'bg-yellow-50',
                            default            => '',
                        };
                        $badgeClass = match(true) {
                            $overdueDays >= 90 => 'bg-red-100 text-red-700',
                            $overdueDays >= 60 => 'bg-orange-100 text-orange-700',
                            $overdueDays >= 30 => 'bg-yellow-100 text-yellow-700',
                            default            => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $rowClass }}">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">
                            {{ $invoice->invoice_number ?? $invoice->number ?? '#'.$invoice->id }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $invoice->customer->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ $overdueDays }} يوم
                            </span>
                        </td>
                        <td class="px-4 py-3 text-left text-gray-700">
                            {{ number_format($invoice->total, 2) }} ج.س
                        </td>
                        <td class="px-4 py-3 text-left text-green-600">
                            {{ number_format($invoice->paid_amount, 2) }} ج.س
                        </td>
                        <td class="px-4 py-3 text-left font-bold text-red-600">
                            {{ number_format($remaining, 2) }} ج.س
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('invoices.show', $invoice->id) }}"
                               class="text-primary hover:underline text-xs">
                                عرض
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <i class="fa fa-check-circle text-4xl text-green-400 mb-3 block"></i>
                            <p class="text-gray-400 font-medium">لا توجد فواتير متأخرة</p>
                            <p class="text-gray-300 text-xs mt-1">جميع الفواتير في حالة جيدة</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($totalInvoices > 0)
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="4" class="px-4 py-3 font-semibold text-gray-700 text-right">الإجمالي</td>
                        <td class="px-4 py-3 text-left font-bold text-gray-700">
                            {{ number_format($overdue->sum('total'), 2) }} ج.س
                        </td>
                        <td class="px-4 py-3 text-left font-bold text-green-600">
                            {{ number_format($overdue->sum('paid_amount'), 2) }} ج.س
                        </td>
                        <td class="px-4 py-3 text-left font-bold text-red-600">
                            {{ number_format($totalDue, 2) }} ج.س
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- مفتاح الألوان --}}
    <div class="flex items-center gap-5 text-xs text-gray-500 pb-2">
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-yellow-300 inline-block"></span> أقل من 30 يوم
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-orange-300 inline-block"></span> 30 – 60 يوم
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-red-300 inline-block"></span> أكثر من 60 يوم
        </span>
    </div>

</div>
@endsection
