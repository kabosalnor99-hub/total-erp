@extends('layouts.app')

@section('title', __('reports.sales_summary') ?? 'ملخص المبيعات')

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">ملخص المبيعات</h1>
            <p class="text-sm text-gray-500 mt-1">عرض إجمالي المبيعات والاتجاهات الشهرية</p>
        </div>
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
            العودة للتقارير
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from', $data['date_from']) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to', $data['date_to']) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 inline-block ml-1"/>
                    تصفية
                </button>
                <a href="{{ route('reports.sales-summary') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">إعادة تعيين</a>
            </div>
        </div>
    </form>

    {{-- Summary Cards --}}
    @php $s = $data['summary']; @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-blue-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي المبيعات</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($s->total_amount ?? 0, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">عدد الفواتير</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($s->total_invoices ?? 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">فاتورة</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي المحصّل</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($s->total_paid ?? 0, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي المتبقي</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($s->total_due ?? 0, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-yellow-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الخصومات</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($s->total_discount ?? 0, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-purple-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الضرائب</p>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($s->total_tax ?? 0, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 col-span-2">
            <p class="text-xs text-gray-500 mb-1">متوسط قيمة الفاتورة</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($s->avg_invoice ?? 0, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
    </div>

    {{-- Monthly Trend Table --}}
    @if($data['monthly']->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">الاتجاه الشهري للمبيعات</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الشهر</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">إجمالي المبيعات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($data['monthly'] as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-800">{{ $row->month }}</td>
                        <td class="px-4 py-3 text-left font-medium text-blue-700">{{ number_format($row->total, 2) }} ج.س</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 text-left font-bold text-blue-700">{{ number_format($data['monthly']->sum('total'), 2) }} ج.س</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 p-10 text-center">
        <x-heroicon-o-chart-bar class="w-12 h-12 text-gray-300 mx-auto mb-3"/>
        <p class="text-gray-400">لا توجد بيانات في الفترة المحددة</p>
    </div>
    @endif

</div>
@endsection
