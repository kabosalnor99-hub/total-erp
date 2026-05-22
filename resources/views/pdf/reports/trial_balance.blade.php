{{-- المسار الكامل: resources/views/reports/trial_balance.blade.php --}}
@extends('layouts.app')

@section('title', 'ميزان المراجعة')

@section('content')
<div class="p-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">ميزان المراجعة</h1>
            <p class="text-sm text-gray-500 mt-1">Trial Balance</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reports.trial-balance', array_merge(request()->query(), ['format' => 'pdf'])) }}"
               target="_blank"
               class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
                <i class="fa fa-file-pdf"></i>
                تصدير PDF
            </a>
        </div>
    </div>

    {{-- فلاتر الفترة --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('reports.trial-balance') }}" class="flex items-end gap-4 flex-wrap">
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

    {{-- بطاقات الإجماليات --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">عدد الحسابات</p>
            <p class="text-2xl font-bold text-primary">{{ count($rows) }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl border border-blue-100 shadow-sm p-4 text-center">
            <p class="text-xs text-blue-500 mb-1">إجمالي المدين</p>
            <p class="text-xl font-bold text-blue-700">{{ number_format($total_debit, 2) }}</p>
        </div>
        <div class="bg-green-50 rounded-xl border border-green-100 shadow-sm p-4 text-center">
            <p class="text-xs text-green-500 mb-1">إجمالي الدائن</p>
            <p class="text-xl font-bold text-green-700">{{ number_format($total_credit, 2) }}</p>
        </div>
        <div class="{{ $is_balanced ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100' }} rounded-xl border shadow-sm p-4 text-center">
            <p class="text-xs {{ $is_balanced ? 'text-green-500' : 'text-red-500' }} mb-1">حالة الميزان</p>
            <p class="text-lg font-bold {{ $is_balanced ? 'text-green-700' : 'text-red-700' }}">
                {{ $is_balanced ? '✓ متوازن' : '✗ غير متوازن' }}
            </p>
        </div>
    </div>

    @if(!$is_balanced)
    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4 text-sm text-red-700 flex items-center gap-2">
        <i class="fa fa-exclamation-triangle"></i>
        <span>تحذير: الميزان غير متوازن — الفارق {{ number_format(abs($total_debit - $total_credit), 2) }} ج.س</span>
    </div>
    @endif

    {{-- جدول ميزان المراجعة --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-white">
                    <th class="text-right py-3 px-4">الكود</th>
                    <th class="text-right py-3 px-4">اسم الحساب</th>
                    <th class="text-right py-3 px-4">النوع</th>
                    <th class="text-left py-3 px-4">المدين</th>
                    <th class="text-left py-3 px-4">الدائن</th>
                    <th class="text-left py-3 px-4">الرصيد</th>
                    <th class="text-center py-3 px-4">نوع الرصيد</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4">
                        <a href="{{ route('accounts.ledger', $row['account']) }}"
                           class="text-primary font-mono text-xs hover:underline">
                            {{ $row['account']->code }}
                        </a>
                    </td>
                    <td class="py-3 px-4 font-medium">{{ $row['account']->name_ar }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded-full text-xs
                            {{ $row['account']->type === 'asset'     ? 'bg-blue-100 text-blue-700'   : '' }}
                            {{ $row['account']->type === 'liability' ? 'bg-red-100 text-red-700'     : '' }}
                            {{ $row['account']->type === 'equity'    ? 'bg-purple-100 text-purple-700': '' }}
                            {{ $row['account']->type === 'revenue'   ? 'bg-green-100 text-green-700' : '' }}
                            {{ $row['account']->type === 'expense'   ? 'bg-orange-100 text-orange-700': '' }}
                        ">
                            {{ $row['account']->type_label }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-left font-mono text-blue-700">
                        {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}
                    </td>
                    <td class="py-3 px-4 text-left font-mono text-green-700">
                        {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}
                    </td>
                    <td class="py-3 px-4 text-left font-mono font-bold">
                        {{ number_format($row['balance'], 2) }}
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $row['balance_type'] === 'debit' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            {{ $row['balance_type'] === 'debit' ? 'مدين' : 'دائن' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-gray-400">
                        <i class="fa fa-inbox text-3xl mb-2 block"></i>
                        لا توجد بيانات في هذه الفترة
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-primary-dark text-white font-bold">
                    <td colspan="3" class="py-3 px-4">الإجمالي</td>
                    <td class="py-3 px-4 text-left font-mono">{{ number_format($total_debit, 2) }}</td>
                    <td class="py-3 px-4 text-left font-mono">{{ number_format($total_credit, 2) }}</td>
                    <td class="py-3 px-4 text-left font-mono">{{ number_format(abs($total_debit - $total_credit), 2) }}</td>
                    <td class="py-3 px-4 text-center">
                        {{ $is_balanced ? '✓ متوازن' : '✗ فارق' }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>
@endsection
