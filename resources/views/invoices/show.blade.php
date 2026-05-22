{{-- المسار: resources/views/invoices/show.blade.php --}}
@extends('layouts.app')

@section('title', 'فاتورة — ' . $invoice->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <i class="fa fa-arrow-right"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold font-mono text-gray-800">{{ $invoice->invoice_number }}</h1>
                    @php $c = $invoice->status_color; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700">
                        {{ $invoice->status_label }}
                    </span>
                    @if($invoice->is_overdue)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 animate-pulse">⚠️ متأخرة</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">{{ $invoice->created_at->format('Y/m/d — H:i') }} • {{ $invoice->user?->name }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
               class="flex items-center gap-1.5 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
                <i class="fa fa-print text-xs"></i> طباعة
            </a>
            <a href="{{ route('invoices.pdf', $invoice) }}"
               class="flex items-center gap-1.5 px-3 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition text-sm">
                <i class="fa fa-file-pdf text-xs"></i> PDF
            </a>
            @if(in_array($invoice->status, ['confirmed','partial']))
            <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}"
               class="flex items-center gap-1.5 px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                <i class="fa fa-money-bill text-xs"></i> تسجيل دفعة
            </a>
            @endif
            @if($invoice->status !== 'cancelled' && $invoice->paid_amount == 0)
            <form action="{{ route('invoices.cancel', $invoice) }}" method="POST"
                  onsubmit="return confirm('هل أنت متأكد من إلغاء هذه الفاتورة؟')">
                @csrf @method('PATCH')
                <button type="submit" class="flex items-center gap-1.5 px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition text-sm">
                    <i class="fa fa-ban text-xs"></i> إلغاء
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- معلومات العميل --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-gray-700 border-b pb-2 mb-3 text-sm">بيانات العميل</h2>
            @if($invoice->customer)
            <div class="space-y-1.5 text-sm">
                <p class="font-medium text-gray-800">{{ $invoice->customer->name }}</p>
                @if($invoice->customer->phone)
                    <p class="text-gray-500 dir-ltr text-right">{{ $invoice->customer->phone }}</p>
                @endif
                @if($invoice->customer->address)
                    <p class="text-gray-500 text-xs">{{ $invoice->customer->address }}</p>
                @endif
            </div>
            @else
                <p class="text-gray-400 text-sm">عميل نقدي</p>
            @endif
        </div>

        {{-- تفاصيل الفاتورة --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-gray-700 border-b pb-2 mb-3 text-sm">تفاصيل الفاتورة</h2>
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">النوع</span>
                    <span>{{ $invoice->type_label }}</span>
                </div>
                @if($invoice->due_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">تاريخ الاستحقاق</span>
                    <span class="{{ $invoice->is_overdue ? 'text-red-600 font-semibold' : '' }}">
                        {{ $invoice->due_date->format('Y/m/d') }}
                    </span>
                </div>
                @endif
                @if($invoice->reference)
                <div class="flex justify-between">
                    <span class="text-gray-500">المرجع</span>
                    <span>{{ $invoice->reference }}</span>
                </div>
                @endif
                @if($invoice->notes)
                <p class="text-gray-500 text-xs mt-2 pt-2 border-t">{{ $invoice->notes }}</p>
                @endif
            </div>
        </div>

        {{-- ملخص مالي --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-gray-700 border-b pb-2 mb-3 text-sm">الملخص المالي</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">الإجمالي الفرعي</span>
                    <span>{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-orange-600">
                    <span>خصم {{ $invoice->discount_percent > 0 ? "({$invoice->discount_percent}%)" : '' }}</span>
                    <span>- {{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-blue-600">
                    <span>ضريبة ({{ $invoice->tax_percent }}%)</span>
                    <span>+ {{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between font-bold text-base border-t pt-2">
                    <span>الإجمالي</span>
                    <span class="text-primary">{{ number_format($invoice->total, 2) }}</span>
                </div>
                <div class="flex justify-between text-green-600">
                    <span>المدفوع</span>
                    <span>{{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
                @if($invoice->remaining_amount > 0)
                <div class="flex justify-between text-red-600 font-semibold border-t pt-1">
                    <span>المتبقي</span>
                    <span>{{ number_format($invoice->remaining_amount, 2) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- بنود الفاتورة --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b font-semibold text-gray-700 text-sm">بنود الفاتورة</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b text-xs font-semibold text-gray-500">
                    <tr>
                        <th class="px-4 py-2 text-right">#</th>
                        <th class="px-4 py-2 text-right">المنتج</th>
                        <th class="px-4 py-2 text-center">الوحدة</th>
                        <th class="px-4 py-2 text-center">الكمية</th>
                        <th class="px-4 py-2 text-center">سعر الوحدة</th>
                        <th class="px-4 py-2 text-center">خصم</th>
                        <th class="px-4 py-2 text-center">الإجمالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($invoice->items as $i => $item)
                    <tr>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-400">{{ $item->product_sku }}</p>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ $item->unit }}</td>
                        <td class="px-4 py-3 text-center font-medium">{{ number_format($item->quantity) }}</td>
                        <td class="px-4 py-3 text-center">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-3 text-center text-orange-600 text-xs">
                            {{ $item->discount_percent > 0 ? $item->discount_percent . '%' : ($item->discount_amount > 0 ? number_format($item->discount_amount, 2) : '—') }}
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- الدفعات --}}
    @if($invoice->payments->count() > 0)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b font-semibold text-gray-700 text-sm">سجل الدفعات</div>
        <div class="divide-y divide-gray-50">
            @foreach($invoice->payments as $payment)
            <div class="flex items-center justify-between px-5 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fa fa-{{ $payment->method_icon }} text-green-600 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $payment->payment_number }}</p>
                        <p class="text-xs text-gray-400">{{ $payment->payment_date->format('Y/m/d') }} — {{ $payment->method_label }} — {{ $payment->user?->name }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-green-600">{{ number_format($payment->amount, 2) }}</p>
                    @if($payment->reference)
                        <p class="text-xs text-gray-400">مرجع: {{ $payment->reference }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
