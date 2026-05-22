{{-- المسار: resources/views/payments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'المدفوعات')

@section('content')
<div class="space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">المدفوعات</h1>
            <p class="text-sm text-gray-500 mt-1">سجل جميع الدفعات المستلمة</p>
        </div>
        @canPermission('payments.create')
        <a href="{{ route('payments.create') }}"
           class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition font-medium">
            <i class="fa fa-plus"></i>
            <span>تسجيل دفعة</span>
        </a>
        @endcanPermission
    </div>

    {{-- رسائل --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- بطاقات الإحصائيات --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">تحصيل اليوم</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($stats['today'], 2) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">SDG</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 mb-1">تحصيل الشهر</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['month'], 2) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">SDG</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-green-100">
            <p class="text-xs text-gray-500 mb-1">نقدي الشهر</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['cash'], 2) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">SDG</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-blue-100">
            <p class="text-xs text-gray-500 mb-1">بنك الشهر</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['bank'], 2) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">SDG</p>
        </div>
    </div>

    {{-- فلاتر --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-40">
                <label class="block text-xs font-medium text-gray-600 mb-1">بحث</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="رقم السند أو اسم العميل..."
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">طريقة الدفع</label>
                <select name="method" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">الكل</option>
                    <option value="cash"   {{ request('method')=='cash'  ?'selected':'' }}>نقدي</option>
                    <option value="bank"   {{ request('method')=='bank'  ?'selected':'' }}>بنك</option>
                    <option value="cheque" {{ request('method')=='cheque'?'selected':'' }}>شيك</option>
                    <option value="other"  {{ request('method')=='other' ?'selected':'' }}>أخرى</option>
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">من تاريخ</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">إلى تاريخ</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition text-sm font-medium">
                    <i class="fa fa-search ml-1"></i> بحث
                </button>
                @if(request()->hasAny(['search','method','from','to']))
                <a href="{{ route('payments.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition text-sm">
                    مسح
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- جدول المدفوعات --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @if($payments->isEmpty())
        <div class="text-center py-16">
            <i class="fa fa-money-bill-wave text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-400">لا توجد دفعات مسجلة</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="px-4 py-3 text-right text-sm font-semibold">رقم السند</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold">العميل</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold">الفاتورة</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold">التاريخ</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold">طريقة الدفع</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold">المبلغ</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($payments as $payment)
                    @php
                        $methodColors = [
                            'cash'   => 'bg-green-100 text-green-700',
                            'bank'   => 'bg-blue-100 text-blue-700',
                            'cheque' => 'bg-purple-100 text-purple-700',
                            'other'  => 'bg-gray-100 text-gray-600',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm font-medium text-primary">{{ $payment->payment_number }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($payment->customer)
                            <a href="{{ route('customers.show', $payment->customer) }}"
                               class="text-sm font-medium text-gray-800 hover:text-primary">
                                {{ $payment->customer->name }}
                            </a>
                            @else
                            <span class="text-sm text-gray-400">عميل نقدي</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($payment->invoice)
                            <a href="{{ route('invoices.show', $payment->invoice) }}"
                               class="font-mono text-xs text-primary hover:underline">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                            @else
                            <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') : $payment->created_at->format('Y/m/d') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-1 rounded-full font-medium {{ $methodColors[$payment->method] ?? 'bg-gray-100 text-gray-600' }}">
                                <i class="fa {{ $payment->method_icon }} ml-1"></i>
                                {{ $payment->method_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-green-700">{{ number_format($payment->amount, 2) }}</span>
                            <span class="text-xs text-gray-400 mr-1">SDG</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('payments.show', $payment) }}"
                                   class="p-1.5 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-lg transition" title="عرض">
                                    <i class="fa fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('payments.print', $payment) }}" target="_blank"
                                   class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition" title="طباعة">
                                    <i class="fa fa-print text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($payments->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $payments->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
