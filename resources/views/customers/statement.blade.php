{{-- المسار الكامل: resources/views/customers/statement.blade.php --}}

@extends('layouts.app')

@section('title', 'كشف حساب: ' . $customer->name)

@section('content')
<div class="space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">كشف حساب العميل</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $customer->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('customers.show', $customer) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                رجوع
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#00838F] text-white rounded-lg hover:bg-[#005F6B] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                طباعة
            </button>
        </div>
    </div>

    {{-- فلتر الفترة --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('customers.statement', $customer) }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
                <input type="date" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00838F] focus:border-[#00838F]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
                <input type="date" name="to" value="{{ request('to', now()->format('Y-m-d')) }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00838F] focus:border-[#00838F]">
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-[#00838F] text-white rounded-lg hover:bg-[#005F6B] transition-colors text-sm">
                عرض
            </button>
        </form>
    </div>

    {{-- بطاقة معلومات العميل --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">بيانات العميل</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">الاسم:</span>
                        <span class="font-medium">{{ $customer->name }}</span>
                    </div>
                    @if($customer->phone)
                    <div class="flex justify-between">
                        <span class="text-gray-500">الهاتف:</span>
                        <span>{{ $customer->phone }}</span>
                    </div>
                    @endif
                    @if($customer->email)
                    <div class="flex justify-between">
                        <span class="text-gray-500">البريد:</span>
                        <span>{{ $customer->email }}</span>
                    </div>
                    @endif
                    @if($customer->address)
                    <div class="flex justify-between">
                        <span class="text-gray-500">العنوان:</span>
                        <span>{{ $customer->address }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">ملخص الحساب</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">إجمالي الفواتير:</span>
                        <span class="font-medium">{{ number_format($summary['total_invoiced'], 2) }} ج.س</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">إجمالي المدفوعات:</span>
                        <span class="font-medium text-green-600">{{ number_format($summary['total_paid'], 2) }} ج.س</span>
                    </div>
                    <div class="flex justify-between border-t pt-2 mt-2">
                        <span class="text-gray-700 font-semibold">الرصيد المستحق:</span>
                        <span class="font-bold {{ $summary['balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($summary['balance'], 2) }} ج.س
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">حد الائتمان</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">الحد المسموح:</span>
                        <span>{{ number_format($customer->credit_limit, 2) }} ج.س</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">المستخدم:</span>
                        <span>{{ number_format($customer->balance, 2) }} ج.س</span>
                    </div>
                    @if($customer->credit_limit > 0)
                    <div class="mt-2">
                        @php $pct = min(100, ($customer->balance / $customer->credit_limit) * 100) @endphp
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ number_format($pct, 0) }}% مستخدم</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- جدول حركات الحساب --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">حركات الحساب</h3>
            <p class="text-sm text-gray-500 mt-1">
                الفترة من {{ \Carbon\Carbon::parse(request('from', now()->startOfMonth()))->format('d/m/Y') }}
                إلى {{ \Carbon\Carbon::parse(request('to', now()))->format('d/m/Y') }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#00838F] text-white">
                    <tr>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">البيان</th>
                        <th class="px-4 py-3 text-right">رقم المرجع</th>
                        <th class="px-4 py-3 text-left">مدين (له)</th>
                        <th class="px-4 py-3 text-left">دائن (عليه)</th>
                        <th class="px-4 py-3 text-left">الرصيد</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    {{-- رصيد افتتاحي --}}
                    <tr class="bg-blue-50">
                        <td class="px-4 py-3 text-gray-500">
                            {{ \Carbon\Carbon::parse(request('from', now()->startOfMonth()))->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 font-medium text-blue-700" colspan="2">رصيد افتتاحي</td>
                        <td class="px-4 py-3 text-left"></td>
                        <td class="px-4 py-3 text-left"></td>
                        <td class="px-4 py-3 text-left font-bold text-blue-700">
                            {{ number_format($summary['opening_balance'], 2) }} ج.س
                        </td>
                    </tr>

                    @php $runningBalance = $summary['opening_balance']; @endphp

                    @forelse($transactions as $txn)
                        @php
                            if ($txn['type'] === 'invoice') {
                                $runningBalance += $txn['amount'];
                                $debit = $txn['amount'];
                                $credit = 0;
                            } else {
                                $runningBalance -= $txn['amount'];
                                $debit = 0;
                                $credit = $txn['amount'];
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $loop->even ? 'bg-gray-50/50' : '' }}">
                            <td class="px-4 py-3 text-gray-600">{{ $txn['date']->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium {{ $txn['type'] === 'invoice' ? 'text-orange-600' : 'text-green-600' }}">
                                    {{ $txn['type'] === 'invoice' ? 'فاتورة مبيعات' : 'دفعة مستلمة' }}
                                </span>
                                @if($txn['notes'] ?? null)
                                    <br><span class="text-xs text-gray-400">{{ $txn['notes'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($txn['type'] === 'invoice')
                                    <a href="{{ route('invoices.show', $txn['id']) }}"
                                       class="text-[#00838F] hover:underline font-mono">{{ $txn['reference'] }}</a>
                                @else
                                    <a href="{{ route('payments.show', $txn['id']) }}"
                                       class="text-[#00838F] hover:underline font-mono">{{ $txn['reference'] }}</a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-left {{ $debit > 0 ? 'text-orange-600 font-medium' : 'text-gray-300' }}">
                                {{ $debit > 0 ? number_format($debit, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-left {{ $credit > 0 ? 'text-green-600 font-medium' : 'text-gray-300' }}">
                                {{ $credit > 0 ? number_format($credit, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-left font-bold {{ $runningBalance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($runningBalance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                لا توجد حركات في هذه الفترة
                            </td>
                        </tr>
                    @endforelse

                    {{-- رصيد ختامي --}}
                    <tr class="bg-gray-100 font-bold">
                        <td class="px-4 py-3" colspan="3">الرصيد الختامي</td>
                        <td class="px-4 py-3 text-left text-orange-700">{{ number_format($summary['total_invoiced'], 2) }}</td>
                        <td class="px-4 py-3 text-left text-green-700">{{ number_format($summary['total_paid'], 2) }}</td>
                        <td class="px-4 py-3 text-left {{ $summary['balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($summary['balance'], 2) }} ج.س
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- الفواتير غير المسددة --}}
    @if($unpaidInvoices->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-red-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-red-100 bg-red-50">
            <h3 class="text-lg font-semibold text-red-800">الفواتير غير المسددة ({{ $unpaidInvoices->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-red-100 text-red-800">
                    <tr>
                        <th class="px-4 py-3 text-right">رقم الفاتورة</th>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">تاريخ الاستحقاق</th>
                        <th class="px-4 py-3 text-left">الإجمالي</th>
                        <th class="px-4 py-3 text-left">المدفوع</th>
                        <th class="px-4 py-3 text-left">المتبقي</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-red-50">
                    @foreach($unpaidInvoices as $invoice)
                    <tr class="hover:bg-red-50/50">
                        <td class="px-4 py-3">
                            <a href="{{ route('invoices.show', $invoice) }}"
                               class="text-[#00838F] hover:underline font-mono font-medium">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $invoice->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 {{ $invoice->due_date && $invoice->due_date->isPast() ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                            {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '—' }}
                            @if($invoice->due_date && $invoice->due_date->isPast())
                                <span class="text-xs bg-red-100 text-red-600 px-1 rounded mr-1">متأخرة</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-left font-medium">{{ number_format($invoice->total, 2) }}</td>
                        <td class="px-4 py-3 text-left text-green-600">{{ number_format($invoice->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 text-left text-red-600 font-bold">{{ number_format($invoice->remaining_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($invoice->status === 'partial')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">جزئي</span>
                            @elseif($invoice->status === 'confirmed')
                                <span class="px-2 py-1 text-xs bg-orange-100 text-orange-700 rounded-full">غير مسدد</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- CSS للطباعة --}}
<style>
@media print {
    aside, nav, .no-print { display: none !important; }
    body { font-family: 'Cairo', sans-serif; direction: rtl; }
    .shadow-sm { box-shadow: none; }
    table { font-size: 11px; }
}
</style>
@endsection
