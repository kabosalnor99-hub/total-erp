{{-- المسار الكامل: resources/views/suppliers/statement.blade.php --}}

@extends('layouts.app')

@section('title', 'كشف حساب — ' . $supplier->name)

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('suppliers.show', $supplier) }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">كشف حساب المورد</h1>
                <p class="text-sm text-gray-500">{{ $supplier->name }}</p>
            </div>
        </div>
        <button onclick="window.print()"
                class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            طباعة
        </button>
    </div>

    {{-- فلتر الفترة --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ $from }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ $to }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
            </div>
            <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                عرض
            </button>
        </form>
    </div>

    {{-- ملخص الحساب --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $totalOrders  = $orders->sum('total');
            $totalPayments = $payments->sum('amount');
            $closingBalance = $openingBalance + $totalOrders - $totalPayments;
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">رصيد الافتتاح</p>
            <p class="text-lg font-bold {{ $openingBalance >= 0 ? 'text-red-500' : 'text-green-600' }}">
                {{ number_format(abs($openingBalance), 2) }}
            </p>
            <p class="text-xs text-gray-400">{{ $openingBalance >= 0 ? 'مدين' : 'دائن' }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي المشتريات</p>
            <p class="text-lg font-bold text-gray-800">{{ number_format($totalOrders, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي المدفوع</p>
            <p class="text-lg font-bold text-green-600">{{ number_format($totalPayments, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">الرصيد الختامي</p>
            <p class="text-lg font-bold {{ $closingBalance >= 0 ? 'text-red-500' : 'text-green-600' }}">
                {{ number_format(abs($closingBalance), 2) }}
            </p>
            <p class="text-xs text-gray-400">{{ $closingBalance >= 0 ? 'مدين' : 'دائن' }}</p>
        </div>
    </div>

    {{-- جدول الحركات --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-5 border-b">
            <h2 class="text-base font-semibold text-gray-700">حركات الحساب</h2>
            <p class="text-xs text-gray-400 mt-0.5">من {{ $from }} إلى {{ $to }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-teal-600 text-white text-xs">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium">التاريخ</th>
                        <th class="px-4 py-3 text-right font-medium">البيان</th>
                        <th class="px-4 py-3 text-right font-medium">مرجع</th>
                        <th class="px-4 py-3 text-right font-medium">مدين</th>
                        <th class="px-4 py-3 text-right font-medium">دائن</th>
                        <th class="px-4 py-3 text-right font-medium">الرصيد</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    {{-- رصيد افتتاحي --}}
                    <tr class="bg-gray-50 font-medium">
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $from }}</td>
                        <td class="px-4 py-3 text-gray-600" colspan="2">رصيد منقول</td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3 font-semibold {{ $openingBalance >= 0 ? 'text-red-500' : 'text-green-600' }}">
                            {{ number_format(abs($openingBalance), 2) }}
                        </td>
                    </tr>

                    @php $runningBalance = $openingBalance; @endphp

                    {{-- دمج الأوامر والمدفوعات وترتيبها --}}
                    @php
                        $transactions = collect();

                        foreach ($orders as $order) {
                            $transactions->push([
                                'date'    => $order->created_at,
                                'desc'    => 'أمر شراء',
                                'ref'     => $order->order_number,
                                'debit'   => $order->total,
                                'credit'  => 0,
                                'link'    => route('purchase-orders.show', $order),
                            ]);
                        }

                        foreach ($payments as $payment) {
                            $transactions->push([
                                'date'    => $payment->payment_date,
                                'desc'    => 'دفعة للمورد',
                                'ref'     => $payment->payment_number ?? '—',
                                'debit'   => 0,
                                'credit'  => $payment->amount,
                                'link'    => null,
                            ]);
                        }

                        $transactions = $transactions->sortBy('date');
                    @endphp

                    @forelse($transactions as $tx)
                    @php
                        $runningBalance += $tx['debit'] - $tx['credit'];
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ \Carbon\Carbon::parse($tx['date'])->format('Y/m/d') }}
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $tx['desc'] }}</td>
                        <td class="px-4 py-3">
                            @if($tx['link'])
                                <a href="{{ $tx['link'] }}" class="text-teal-600 hover:underline font-mono text-xs">{{ $tx['ref'] }}</a>
                            @else
                                <span class="font-mono text-xs text-gray-500">{{ $tx['ref'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-red-500 font-medium">
                            {{ $tx['debit'] > 0 ? number_format($tx['debit'], 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-green-600 font-medium">
                            {{ $tx['credit'] > 0 ? number_format($tx['credit'], 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 font-semibold {{ $runningBalance >= 0 ? 'text-red-500' : 'text-green-600' }}">
                            {{ number_format(abs($runningBalance), 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">لا توجد حركات في هذه الفترة</td>
                    </tr>
                    @endforelse

                    {{-- رصيد ختامي --}}
                    <tr class="bg-teal-50 font-semibold">
                        <td colspan="3" class="px-4 py-3 text-teal-700">الرصيد الختامي</td>
                        <td class="px-4 py-3 text-red-500">{{ number_format($totalOrders, 2) }}</td>
                        <td class="px-4 py-3 text-green-600">{{ number_format($totalPayments, 2) }}</td>
                        <td class="px-4 py-3 text-teal-700 text-base">
                            {{ number_format(abs($closingBalance), 2) }}
                            <span class="text-xs font-normal">{{ $closingBalance >= 0 ? 'مدين' : 'دائن' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
@media print {
    aside, header, form, button { display: none !important; }
    .bg-white { box-shadow: none !important; border: 1px solid #e5e7eb !important; }
}
</style>
@endsection
