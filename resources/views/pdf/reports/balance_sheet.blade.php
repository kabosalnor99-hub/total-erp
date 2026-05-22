{{-- المسار الكامل: resources/views/reports/balance_sheet.blade.php --}}
@extends('layouts.app')

@section('title', 'الميزانية العمومية')

@section('content')
<div class="p-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">الميزانية العمومية</h1>
            <p class="text-sm text-gray-500 mt-1">Balance Sheet — بتاريخ {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('reports.balance-sheet', array_merge(request()->query(), ['format' => 'pdf'])) }}"
           target="_blank"
           class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
            <i class="fa fa-file-pdf"></i>
            تصدير PDF
        </a>
    </div>

    {{-- فلتر التاريخ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('reports.balance-sheet') }}" class="flex items-end gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">بتاريخ</label>
                <input type="date" name="to_date" value="{{ $to_date }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <button type="submit"
                class="bg-primary text-white px-5 py-2 rounded-lg hover:bg-primary-dark transition text-sm">
                <i class="fa fa-search me-1"></i> عرض
            </button>
        </form>
    </div>

    {{-- بطاقات الإجماليات --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
            <p class="text-xs text-blue-600 mb-1">إجمالي الأصول</p>
            <p class="text-xl font-bold text-blue-700">{{ number_format($total_assets, 2) }}</p>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
            <p class="text-xs text-amber-600 mb-1">إجمالي الخصوم</p>
            <p class="text-xl font-bold text-amber-700">{{ number_format($total_liabilities, 2) }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-100 rounded-xl p-4 text-center">
            <p class="text-xs text-purple-600 mb-1">حقوق الملكية</p>
            <p class="text-xl font-bold text-purple-700">{{ number_format($total_equity, 2) }}</p>
        </div>
        <div class="{{ $is_balanced ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100' }} border rounded-xl p-4 text-center">
            <p class="text-xs {{ $is_balanced ? 'text-green-600' : 'text-red-600' }} mb-1">حالة الميزانية</p>
            <p class="text-lg font-bold {{ $is_balanced ? 'text-green-700' : 'text-red-700' }}">
                {{ $is_balanced ? '✓ متوازنة' : '✗ غير متوازنة' }}
            </p>
        </div>
    </div>

    {{-- معادلة الميزانية --}}
    <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 mb-6 text-center">
        <p class="text-xs text-gray-500 mb-2">معادلة الميزانية العمومية</p>
        <p class="text-lg font-bold text-primary">
            <span class="text-blue-700">الأصول ({{ number_format($total_assets, 2) }})</span>
            <span class="text-gray-500 mx-2">=</span>
            <span class="text-amber-700">الخصوم ({{ number_format($total_liabilities, 2) }})</span>
            <span class="text-gray-500 mx-2">+</span>
            <span class="text-purple-700">حقوق الملكية ({{ number_format($total_equity, 2) }})</span>
        </p>
    </div>

    {{-- عمودان: الأصول | الخصوم وحقوق الملكية --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- الأصول --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-blue-700 text-white px-4 py-3 flex items-center justify-between">
                <span class="font-semibold">الأصول — Assets</span>
                <span class="text-blue-200 text-sm">{{ number_format($total_assets, 2) }} ج.س</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="text-right py-2 px-4 text-blue-700">الكود</th>
                        <th class="text-right py-2 px-4 text-blue-700">الحساب</th>
                        <th class="text-left py-2 px-4 text-blue-700">الرصيد</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $row)
                    <tr class="border-b border-gray-100 hover:bg-blue-50/50">
                        <td class="py-2 px-4 font-mono text-xs text-primary">{{ $row['account']->code }}</td>
                        <td class="py-2 px-4">{{ $row['account']->name_ar }}</td>
                        <td class="py-2 px-4 text-left font-mono font-medium text-blue-700">
                            {{ number_format($row['balance'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-6 text-center text-gray-400 text-xs">لا توجد أصول</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-blue-700 text-white font-bold">
                        <td colspan="2" class="py-3 px-4">إجمالي الأصول</td>
                        <td class="py-3 px-4 text-left font-mono">{{ number_format($total_assets, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- الخصوم وحقوق الملكية --}}
        <div class="space-y-4">

            {{-- الخصوم --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-amber-700 text-white px-4 py-3 flex items-center justify-between">
                    <span class="font-semibold">الخصوم — Liabilities</span>
                    <span class="text-amber-200 text-sm">{{ number_format($total_liabilities, 2) }} ج.س</span>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-amber-50">
                        <tr>
                            <th class="text-right py-2 px-4 text-amber-700">الكود</th>
                            <th class="text-right py-2 px-4 text-amber-700">الحساب</th>
                            <th class="text-left py-2 px-4 text-amber-700">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($liabilities as $row)
                        <tr class="border-b border-gray-100 hover:bg-amber-50/50">
                            <td class="py-2 px-4 font-mono text-xs text-primary">{{ $row['account']->code }}</td>
                            <td class="py-2 px-4">{{ $row['account']->name_ar }}</td>
                            <td class="py-2 px-4 text-left font-mono font-medium text-amber-700">
                                {{ number_format($row['balance'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-400 text-xs">لا توجد خصوم</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-amber-700 text-white font-bold">
                            <td colspan="2" class="py-3 px-4">إجمالي الخصوم</td>
                            <td class="py-3 px-4 text-left font-mono">{{ number_format($total_liabilities, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- حقوق الملكية --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-purple-700 text-white px-4 py-3 flex items-center justify-between">
                    <span class="font-semibold">حقوق الملكية — Equity</span>
                    <span class="text-purple-200 text-sm">{{ number_format($total_equity, 2) }} ج.س</span>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-purple-50">
                        <tr>
                            <th class="text-right py-2 px-4 text-purple-700">الكود</th>
                            <th class="text-right py-2 px-4 text-purple-700">الحساب</th>
                            <th class="text-left py-2 px-4 text-purple-700">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equity as $row)
                        <tr class="border-b border-gray-100 hover:bg-purple-50/50">
                            <td class="py-2 px-4 font-mono text-xs text-primary">{{ $row['account']->code }}</td>
                            <td class="py-2 px-4">{{ $row['account']->name_ar }}</td>
                            <td class="py-2 px-4 text-left font-mono font-medium text-purple-700">
                                {{ number_format($row['balance'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-400 text-xs">لا توجد حقوق ملكية</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-purple-700 text-white font-bold">
                            <td colspan="2" class="py-3 px-4">إجمالي حقوق الملكية</td>
                            <td class="py-3 px-4 text-left font-mono">{{ number_format($total_equity, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- إجمالي الخصوم + حقوق الملكية --}}
            <div class="bg-primary text-white rounded-xl p-4 flex justify-between items-center">
                <span class="font-bold">إجمالي الخصوم + حقوق الملكية</span>
                <span class="font-mono font-bold text-xl">{{ number_format($total_liabilities + $total_equity, 2) }} ج.س</span>
            </div>

        </div>
    </div>

</div>
@endsection
