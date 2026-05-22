{{-- المسار الكامل: resources/views/reports/income_statement.blade.php --}}
@extends('layouts.app')

@section('title', 'قائمة الدخل')

@section('content')
<div class="p-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">قائمة الدخل</h1>
            <p class="text-sm text-gray-500 mt-1">Income Statement</p>
        </div>
        <a href="{{ route('reports.income-statement', array_merge(request()->query(), ['format' => 'pdf'])) }}"
           target="_blank"
           class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
            <i class="fa fa-file-pdf"></i>
            تصدير PDF
        </a>
    </div>

    {{-- فلاتر --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('reports.income-statement') }}" class="flex items-end gap-4 flex-wrap">
            <div>
                <label class="block text-xs text-gray-500 mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ $from_date }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ $to_date }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <button type="submit"
                class="bg-primary text-white px-5 py-2 rounded-lg hover:bg-primary-dark transition text-sm">
                <i class="fa fa-search me-1"></i> عرض
            </button>
        </form>
    </div>

    {{-- بطاقة النتيجة --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
            <p class="text-xs text-green-600 mb-1">إجمالي الإيرادات</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($total_revenues, 2) }}</p>
            <p class="text-xs text-green-500 mt-1">ج.س</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
            <p class="text-xs text-red-600 mb-1">إجمالي المصروفات</p>
            <p class="text-2xl font-bold text-red-700">{{ number_format($total_expenses, 2) }}</p>
            <p class="text-xs text-red-500 mt-1">ج.س</p>
        </div>
        <div class="{{ $is_profit ? 'bg-primary/10 border-primary/20' : 'bg-red-100 border-red-200' }} border rounded-xl p-4 text-center">
            <p class="text-xs {{ $is_profit ? 'text-primary' : 'text-red-600' }} mb-1">
                {{ $is_profit ? 'صافي الربح' : 'صافي الخسارة' }}
            </p>
            <p class="text-2xl font-bold {{ $is_profit ? 'text-primary' : 'text-red-700' }}">
                {{ number_format(abs($net_income), 2) }}
            </p>
            <p class="text-xs {{ $is_profit ? 'text-primary/70' : 'text-red-500' }} mt-1">ج.س</p>
        </div>
    </div>

    {{-- عمودان: الإيرادات / المصروفات --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- الإيرادات --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-green-700 text-white px-4 py-3 flex items-center gap-2">
                <i class="fa fa-arrow-up"></i>
                <span class="font-semibold">الإيرادات — Revenues</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-green-50">
                    <tr>
                        <th class="text-right py-2 px-4 text-green-700">الكود</th>
                        <th class="text-right py-2 px-4 text-green-700">الحساب</th>
                        <th class="text-left py-2 px-4 text-green-700">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revenues as $row)
                    <tr class="border-b border-gray-100 hover:bg-green-50/50">
                        <td class="py-2 px-4 font-mono text-xs text-primary">{{ $row['account']->code }}</td>
                        <td class="py-2 px-4">{{ $row['account']->name_ar }}</td>
                        <td class="py-2 px-4 text-left font-mono text-green-700 font-medium">
                            {{ number_format($row['balance'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-gray-400 text-xs">لا توجد إيرادات</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-green-700 text-white font-bold">
                        <td colspan="2" class="py-3 px-4">إجمالي الإيرادات</td>
                        <td class="py-3 px-4 text-left font-mono">{{ number_format($total_revenues, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- المصروفات --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-red-700 text-white px-4 py-3 flex items-center gap-2">
                <i class="fa fa-arrow-down"></i>
                <span class="font-semibold">المصروفات — Expenses</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-red-50">
                    <tr>
                        <th class="text-right py-2 px-4 text-red-700">الكود</th>
                        <th class="text-right py-2 px-4 text-red-700">الحساب</th>
                        <th class="text-left py-2 px-4 text-red-700">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $row)
                    <tr class="border-b border-gray-100 hover:bg-red-50/50">
                        <td class="py-2 px-4 font-mono text-xs text-primary">{{ $row['account']->code }}</td>
                        <td class="py-2 px-4">{{ $row['account']->name_ar }}</td>
                        <td class="py-2 px-4 text-left font-mono text-red-700 font-medium">
                            {{ number_format($row['balance'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-gray-400 text-xs">لا توجد مصروفات</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-red-700 text-white font-bold">
                        <td colspan="2" class="py-3 px-4">إجمالي المصروفات</td>
                        <td class="py-3 px-4 text-left font-mono">{{ number_format($total_expenses, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    {{-- ملخص النتيجة --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-md mx-auto">
        <h3 class="font-bold text-gray-700 mb-4 text-center">ملخص النتيجة</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">إجمالي الإيرادات</span>
                <span class="font-mono font-bold text-green-700">{{ number_format($total_revenues, 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span class="text-gray-600">إجمالي المصروفات</span>
                <span class="font-mono font-bold text-red-700">({{ number_format($total_expenses, 2) }})</span>
            </div>
            <div class="flex justify-between items-center py-3 rounded-lg {{ $is_profit ? 'bg-primary/10' : 'bg-red-50' }} px-3">
                <span class="font-bold {{ $is_profit ? 'text-primary' : 'text-red-700' }}">
                    {{ $is_profit ? '✓ صافي الربح' : '✗ صافي الخسارة' }}
                </span>
                <span class="font-mono font-bold text-xl {{ $is_profit ? 'text-primary' : 'text-red-700' }}">
                    {{ number_format(abs($net_income), 2) }}
                </span>
            </div>
        </div>
    </div>

</div>
@endsection
