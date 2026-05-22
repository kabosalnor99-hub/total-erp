{{-- المسار: resources/views/payments/show.blade.php --}}
@extends('layouts.app')

@section('title', 'سند القبض — ' . $payment->payment_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">سند قبض</h1>
            <p class="font-mono text-primary font-semibold mt-0.5">{{ $payment->payment_number }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('payments.print', $payment) }}" target="_blank"
               class="flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                <i class="fa fa-print"></i><span>طباعة</span>
            </a>
            <a href="{{ route('payments.index') }}"
               class="flex items-center gap-2 text-gray-600 hover:text-gray-800 px-4 py-2 border rounded-lg transition">
                <i class="fa fa-arrow-right"></i><span>رجوع</span>
            </a>
        </div>
    </div>

    {{-- بطاقة التفاصيل --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">

        {{-- شريط الحالة العلوي --}}
        <div class="bg-green-600 text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa fa-check text-white"></i>
                </div>
                <div>
                    <p class="font-bold text-lg">{{ number_format($payment->amount, 2) }} SDG</p>
                    <p class="text-green-100 text-sm">تم استلام الدفعة بنجاح</p>
                </div>
            </div>
            <span class="bg-white/20 text-white text-sm px-3 py-1 rounded-full font-medium">
                <i class="fa {{ $payment->method_icon }} ml-1"></i>
                {{ $payment->method_label }}
            </span>
        </div>

        <div class="p-6 space-y-5">

            {{-- معلومات العميل والفاتورة --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">بيانات العميل</h3>
                    @if($payment->customer)
                    <p class="font-bold text-gray-800">{{ $payment->customer->name }}</p>
                    @if($payment->customer->phone)
                        <p class="text-sm text-gray-500 mt-1">{{ $payment->customer->phone }}</p>
                    @endif
                    @else
                    <p class="text-gray-400 text-sm">عميل نقدي</p>
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">تفاصيل الدفعة</h3>
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">رقم السند</span>
                            <span class="font-mono font-medium text-primary">{{ $payment->payment_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">تاريخ الدفع</span>
                            <span class="font-medium">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') : $payment->created_at->format('Y/m/d') }}</span>
                        </div>
                        @if($payment->reference)
                        <div class="flex justify-between">
                            <span class="text-gray-500">رقم المرجع</span>
                            <span class="font-medium font-mono">{{ $payment->reference }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500">سجّل بواسطة</span>
                            <span class="font-medium">{{ $payment->user?->name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-gray-100">

            {{-- الفاتورة المرتبطة --}}
            @if($payment->invoice)
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">الفاتورة المرتبطة</h3>
                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
                    <div>
                        <p class="font-mono font-medium text-primary">{{ $payment->invoice->invoice_number }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            إجمالي: {{ number_format($payment->invoice->total, 2) }} SDG •
                            متبقي: {{ number_format($payment->invoice->remaining_amount, 2) }} SDG
                        </p>
                    </div>
                    <a href="{{ route('invoices.show', $payment->invoice) }}"
                       class="text-primary text-sm hover:underline font-medium">
                        عرض الفاتورة <i class="fa fa-arrow-left mr-1"></i>
                    </a>
                </div>
            </div>
            @endif

            {{-- ملاحظات --}}
            @if($payment->notes)
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">ملاحظات</h3>
                <p class="text-sm text-gray-700 bg-gray-50 rounded-lg px-4 py-3">{{ $payment->notes }}</p>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection
