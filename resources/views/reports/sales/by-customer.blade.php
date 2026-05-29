@extends('layouts.app')

@section('title', 'المبيعات حسب العميل')

@section('content')
<div class="p-6" dir="rtl">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">المبيعات حسب العميل</h1>
            <p class="text-sm text-gray-500 mt-1">تحليل المبيعات مصنّفةً حسب كل عميل</p>
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
                <a href="{{ route('reports.sales-by-customer') }}" class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">إعادة تعيين</a>
            </div>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-blue-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي المبيعات</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($data['rows']->sum('total_amount'), 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">عدد العملاء</p>
            <p class="text-2xl font-bold text-gray-800">{{ $data['rows']->count() }}</p>
            <p class="text-xs text-gray-400 mt-1">عميل</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الرصيد المتبقي</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($data['rows']->sum('balance'), 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">ج.س</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700">تفصيل المبيعات حسب العميل</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">#</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">العميل</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">عدد الفواتير</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">إجمالي المبيعات</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">الرصيد المتبقي</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">النسبة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $grandTotal = $data['rows']->sum('total_amount') ?: 1; @endphp
                    @forelse($data['rows'] as $i => $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-800">{{ $row->customer->name ?? 'عميل محذوف' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $row->total_invoices }}</td>
                        <td class="px-4 py-3 text-left font-medium text-blue-700">{{ number_format($row->total_amount, 2) }} ج.س</td>
                        <td class="px-4 py-3 text-left">
                            @if($row->balance > 0)
                                <span class="text-red-600 font-medium">{{ number_format($row->balance, 2) }} ج.س</span>
                            @else
                                <span class="text-green-600 text-xs">مسدّد</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $pct = round(($row->total_amount / $grandTotal) * 100, 1); @endphp
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">لا توجد بيانات في الفترة المحددة</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($data['rows']->isNotEmpty())
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="2" class="px-4 py-3 font-semibold text-gray-700">الإجمالي</td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $data['rows']->sum('total_invoices') }}</td>
                        <td class="px-4 py-3 text-left font-bold text-blue-700">{{ number_format($data['rows']->sum('total_amount'), 2) }} ج.س</td>
                        <td class="px-4 py-3 text-left font-bold text-red-600">{{ number_format($data['rows']->sum('balance'), 2) }} ج.س</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
