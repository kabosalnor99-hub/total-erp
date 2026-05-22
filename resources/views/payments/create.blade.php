{{-- المسار: resources/views/payments/create.blade.php --}}
@extends('layouts.app')

@section('title', 'تسجيل دفعة جديدة')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">تسجيل دفعة جديدة</h1>
            @if($invoice)
                <p class="text-sm text-gray-500 mt-1">على فاتورة: <span class="font-mono font-medium text-primary">{{ $invoice->invoice_number }}</span></p>
            @endif
        </div>
        <a href="{{ $invoice ? route('invoices.show', $invoice) : route('payments.index') }}"
           class="flex items-center gap-2 text-gray-600 hover:text-gray-800 px-4 py-2 border rounded-lg transition">
            <i class="fa fa-arrow-right"></i><span>رجوع</span>
        </a>
    </div>

    {{-- رسائل --}}
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fa fa-circle-exclamation"></i> {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('payments.store') }}" x-data="paymentForm()">
        @csrf

        {{-- بطاقة الفاتورة --}}
        @if($invoice)
        <div class="bg-primary/5 border border-primary/20 rounded-xl p-5">
            <h2 class="font-bold text-primary mb-3 text-sm">تفاصيل الفاتورة</h2>
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-500">العميل</span>
                    <p class="font-medium text-gray-800">{{ $invoice->customer?->name ?? 'نقدي' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">رقم الفاتورة</span>
                    <p class="font-mono font-medium text-primary">{{ $invoice->invoice_number }}</p>
                </div>
                <div>
                    <span class="text-gray-500">إجمالي الفاتورة</span>
                    <p class="font-bold text-gray-800">{{ number_format($invoice->total, 2) }} SDG</p>
                </div>
                <div>
                    <span class="text-gray-500">المتبقي للسداد</span>
                    <p class="font-bold text-red-600">{{ number_format($invoice->remaining_amount, 2) }} SDG</p>
                </div>
            </div>
        </div>
        @else
        {{-- اختيار الفاتورة يدوياً --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                رقم الفاتورة <span class="text-red-500">*</span>
            </label>
            <input type="text" name="invoice_id" placeholder="أدخل رقم الفاتورة أو ID..."
                   value="{{ old('invoice_id') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
            <p class="text-xs text-gray-400 mt-1">مثال: ادخل الـ ID الرقمي للفاتورة</p>
        </div>
        @endif

        {{-- بيانات الدفعة --}}
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <h2 class="font-bold text-gray-700 border-b pb-2">بيانات الدفعة</h2>

            {{-- المبلغ --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    المبلغ المدفوع (SDG) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="amount" id="amount"
                           value="{{ old('amount', $invoice?->remaining_amount) }}"
                           min="0.01" step="0.01" required
                           @if($invoice) max="{{ $invoice->remaining_amount }}" @endif
                           x-model="amount"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary text-lg font-bold">
                    <span class="absolute left-3 top-2.5 text-gray-400 text-sm">SDG</span>
                </div>
                @if($invoice && $invoice->remaining_amount > 0)
                <div class="flex gap-2 mt-2">
                    <button type="button"
                            @click="amount = {{ $invoice->remaining_amount }}"
                            class="text-xs px-3 py-1 bg-green-50 text-green-700 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        السداد الكامل ({{ number_format($invoice->remaining_amount, 2) }})
                    </button>
                </div>
                @endif
            </div>

            {{-- تاريخ الدفع --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    تاريخ الدفع <span class="text-red-500">*</span>
                </label>
                <input type="date" name="payment_date" required
                       value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            {{-- طريقة الدفع --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    طريقة الدفع <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach([
                        ['value'=>'cash',   'label'=>'نقدي',  'icon'=>'fa-money-bill-wave', 'color'=>'green'],
                        ['value'=>'bank',   'label'=>'تحويل بنكي','icon'=>'fa-university',  'color'=>'blue'],
                        ['value'=>'cheque', 'label'=>'شيك',   'icon'=>'fa-money-check',     'color'=>'purple'],
                        ['value'=>'other',  'label'=>'أخرى',  'icon'=>'fa-ellipsis',        'color'=>'gray'],
                    ] as $m)
                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition
                                  {{ old('method','cash')===$m['value'] ? 'border-primary bg-primary/5' : 'border-gray-200' }}">
                        <input type="radio" name="method" value="{{ $m['value'] }}"
                               {{ old('method','cash')===$m['value'] ? 'checked' : '' }}
                               class="text-primary focus:ring-primary">
                        <i class="fa {{ $m['icon'] }} text-{{ $m['color'] }}-500"></i>
                        <span class="text-sm font-medium text-gray-700">{{ $m['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- رقم المرجع --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">رقم المرجع / الشيك</label>
                <input type="text" name="reference" value="{{ old('reference') }}"
                       placeholder="رقم الشيك أو رقم العملية البنكية..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            {{-- ملاحظات --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                          placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- زر الحفظ --}}
        <div class="bg-white rounded-xl shadow-sm p-4">
            <button type="submit"
                    class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-bold text-base flex items-center justify-center gap-2">
                <i class="fa fa-check-circle"></i>
                تسجيل الدفعة
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
function paymentForm() {
    return {
        amount: {{ $invoice?->remaining_amount ?? 0 }},
    };
}
</script>
@endpush
@endsection
