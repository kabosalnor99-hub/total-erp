{{-- المسار الكامل: resources/views/accounts/ledger.blade.php --}}
@extends('layouts.app')

@section('title', 'دفتر الأستاذ — ' . $account->name_ar)

@section('content')
<div class="p-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">دفتر الأستاذ</h1>
            <p class="text-lg text-primary font-medium mt-1">
                {{ $account->code }} — {{ $account->name_ar }}
            </p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()"
                    class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm no-print">
                <i class="fa fa-print"></i> طباعة
            </button>
            <a href="{{ route('accounts.index') }}"
               class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm no-print">
                <i class="fa fa-arrow-right"></i> العودة
            </a>
        </div>
    </div>

    {{-- فلتر الفترة --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5 no-print">
        <form method="GET" action="{{ route('accounts.ledger', $account) }}"
              class="flex flex-wrap gap-3 items-end">
            <div class="min-w-[140px]">
                <label class="block text-xs text-gray-500 mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ $fromDate }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs text-gray-500 mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ $toDate }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
            </div>
            <button type="submit"
                    class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-dark transition">
                <i class="fa fa-filter ml-1"></i> تطبيق
            </button>
        </form>
    </div>

    {{-- بطاقات الإجمالي --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 mb-1">نوع الحساب</p>
            <span class="px-2 py-1 rounded-full text-xs font-semibold
                {{ $account->type === 'asset' ? 'bg-blue-100 text-blue-700' :
                  ($account->type === 'liability' ? 'bg-red-100 text-red-700' :
                  ($account->type === 'revenue' ? 'bg-green-100 text-green-700' :
                  ($account->type === 'expense' ? 'bg-orange-100 text-orange-700' : 'bg-purple-100 text-purple-700'))) }}">
                {{ $account->type_label }}
            </span>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي المدين</p>
            <p class="text-xl font-bold font-mono text-gray-800">
                {{ number_format(collect($ledger['rows'])->sum('debit'), 2) }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 mb-1">إجمالي الدائن</p>
            <p class="text-xl font-bold font-mono text-gray-800">
                {{ number_format(collect($ledger['rows'])->sum('credit'), 2) }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 mb-1">الرصيد الختامي</p>
            @php
                $finalBalance = count($ledger['rows']) > 0
                    ? last($ledger['rows'])['balance']
                    : $account->opening_balance ?? 0;
            @endphp
            <p class="text-xl font-bold font-mono {{ $finalBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format(abs($finalBalance), 2) }}
                <span class="text-xs font-normal">
                    {{ $finalBalance >= 0 ? ($account->normal_balance === 'debit' ? 'مدين' : 'دائن') : ($account->normal_balance === 'debit' ? 'دائن' : 'مدين') }}
                </span>
            </p>
        </div>
    </div>

    {{-- جدول دفتر الأستاذ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- رأس للطباعة --}}
        <div class="hidden print:block px-6 py-4 border-b border-gray-200 text-center">
            <h2 class="text-lg font-bold">توتال الكلاكلة — دفتر الأستاذ</h2>
            <p class="text-sm text-gray-600">{{ $account->code }} — {{ $account->name_ar }}</p>
            <p class="text-xs text-gray-400">الفترة: {{ $fromDate }} إلى {{ $toDate }}</p>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-white">
                    <th class="px-4 py-3 text-right font-medium">التاريخ</th>
                    <th class="px-4 py-3 text-right font-medium">رقم القيد</th>
                    <th class="px-4 py-3 text-right font-medium">البيان</th>
                    <th class="px-4 py-3 text-center font-medium w-32">مدين</th>
                    <th class="px-4 py-3 text-center font-medium w-32">دائن</th>
                    <th class="px-4 py-3 text-center font-medium w-36">الرصيد</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                {{-- رصيد افتتاحي --}}
                @if($account->opening_balance > 0)
                <tr class="bg-blue-50/50">
                    <td class="px-4 py-2 text-gray-500 text-xs">—</td>
                    <td class="px-4 py-2 text-gray-400 text-xs">—</td>
                    <td class="px-4 py-2 text-gray-600 font-medium text-xs">رصيد افتتاحي</td>
                    <td class="px-4 py-2 text-center font-mono text-xs">
                        {{ $account->opening_balance_type === 'debit' ? number_format($account->opening_balance, 2) : '—' }}
                    </td>
                    <td class="px-4 py-2 text-center font-mono text-xs">
                        {{ $account->opening_balance_type === 'credit' ? number_format($account->opening_balance, 2) : '—' }}
                    </td>
                    <td class="px-4 py-2 text-center font-mono font-semibold text-xs text-gray-700">
                        {{ number_format($account->opening_balance, 2) }}
                    </td>
                </tr>
                @endif

                @forelse($ledger['rows'] as $row)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($row['date'])->format('Y/m/d') }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('journal.show', $row['entry']) }}"
                           class="font-mono text-primary text-xs hover:underline">
                            {{ $row['entry']->entry_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-700 text-xs">
                        {{ $row['description'] }}
                    </td>
                    <td class="px-4 py-3 text-center font-mono text-xs">
                        @if($row['debit'] > 0)
                            <span class="text-gray-800 font-semibold">{{ number_format($row['debit'], 2) }}</span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-mono text-xs">
                        @if($row['credit'] > 0)
                            <span class="text-gray-800 font-semibold">{{ number_format($row['credit'], 2) }}</span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-mono font-bold text-sm
                        {{ $row['balance'] >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                        {{ number_format(abs($row['balance']), 2) }}
                        <span class="text-xs font-normal text-gray-400">
                            {{ $row['balance'] >= 0 ? 'د' : 'ن' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                        <i class="fa fa-book-open text-4xl mb-3 block opacity-30"></i>
                        لا توجد حركات في هذه الفترة
                    </td>
                </tr>
                @endforelse
            </tbody>

            @if(count($ledger['rows']) > 0)
            <tfoot>
                <tr class="bg-primary/5 font-bold border-t-2 border-primary/20">
                    <td colspan="3" class="px-4 py-3 text-gray-700 text-sm">الإجمالي</td>
                    <td class="px-4 py-3 text-center font-mono text-gray-900">
                        {{ number_format(collect($ledger['rows'])->sum('debit'), 2) }}
                    </td>
                    <td class="px-4 py-3 text-center font-mono text-gray-900">
                        {{ number_format(collect($ledger['rows'])->sum('credit'), 2) }}
                    </td>
                    <td class="px-4 py-3 text-center font-mono font-bold
                        {{ $finalBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format(abs($finalBalance), 2) }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

</div>

<style>
@media print {
    .no-print { display: none !important; }
    nav, header { display: none !important; }
    body { font-size: 11px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 4px 8px; }
}
</style>
@endsection
