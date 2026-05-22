{{-- المسار الكامل: resources/views/vouchers/create.blade.php --}}
@extends('layouts.app')

@section('title', $type === 'receipt' ? 'سند قبض جديد' : 'سند صرف جديد')

@section('content')
<div class="p-6" x-data="{ paymentMethod: '{{ old('payment_method', 'cash') }}' }">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                {{ $type === 'receipt' ? 'سند قبض جديد' : 'سند صرف جديد' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $type === 'receipt' ? 'تسجيل مبلغ مستلم' : 'تسجيل مبلغ مصروف' }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('vouchers.index') }}"
               class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                <i class="fa fa-arrow-right"></i>
                العودة للقائمة
            </a>
        </div>
    </div>

    {{-- تبديل بين القبض والصرف --}}
    <div class="flex gap-2 mb-6">
        <a href="{{ route('vouchers.create', ['type' => 'receipt']) }}"
           class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition
               {{ $type === 'receipt' ? 'bg-green-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50' }}">
            <i class="fa fa-arrow-down"></i> سند قبض
        </a>
        <a href="{{ route('vouchers.create', ['type' => 'payment']) }}"
           class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition
               {{ $type === 'payment' ? 'bg-red-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50' }}">
            <i class="fa fa-arrow-up"></i> سند صرف
        </a>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('vouchers.store') }}">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- النموذج --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- بيانات السند --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fa fa-info-circle text-primary"></i>
                        بيانات السند
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                التاريخ <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                المبلغ <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="amount" value="{{ old('amount') }}"
                                   min="0.01" step="0.01" placeholder="0.00"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $type === 'receipt' ? 'المستلم من / الحساب المدين' : 'المدفوع لـ / الحساب المدين' }}
                                <span class="text-red-500">*</span>
                            </label>
                            <select name="account_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none"
                                    required>
                                <option value="">اختر الحساب...</option>
                                @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} — {{ $account->name_ar }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                حساب الصندوق / البنك <span class="text-red-500">*</span>
                            </label>
                            <select name="cash_account_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none"
                                    required>
                                <option value="">اختر الحساب...</option>
                                @foreach($cashAccounts as $cashAccount)
                                <option value="{{ $cashAccount->id }}" {{ old('cash_account_id') == $cashAccount->id ? 'selected' : '' }}>
                                    {{ $cashAccount->code }} — {{ $cashAccount->name_ar }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                البيان / الوصف <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="description" value="{{ old('description') }}"
                                   placeholder="{{ $type === 'receipt' ? 'مثال: تحصيل فاتورة رقم...' : 'مثال: دفع مصاريف...' }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none"
                                   required>
                        </div>

                    </div>
                </div>

                {{-- طريقة الدفع --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fa fa-credit-card text-primary"></i>
                        طريقة الدفع
                    </h3>

                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="cash"
                                   x-model="paymentMethod"
                                   class="sr-only peer" {{ old('payment_method', 'cash') === 'cash' ? 'checked' : '' }}>
                            <div class="flex flex-col items-center gap-2 p-3 border-2 rounded-lg transition
                                        peer-checked:border-primary peer-checked:bg-primary/5 border-gray-200 hover:border-gray-300">
                                <i class="fa fa-money-bill-wave text-xl text-gray-500 peer-checked:text-primary"></i>
                                <span class="text-sm font-medium text-gray-700">نقدي</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="bank"
                                   x-model="paymentMethod"
                                   class="sr-only peer" {{ old('payment_method') === 'bank' ? 'checked' : '' }}>
                            <div class="flex flex-col items-center gap-2 p-3 border-2 rounded-lg transition
                                        peer-checked:border-primary peer-checked:bg-primary/5 border-gray-200 hover:border-gray-300">
                                <i class="fa fa-university text-xl text-gray-500"></i>
                                <span class="text-sm font-medium text-gray-700">تحويل بنكي</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="cheque"
                                   x-model="paymentMethod"
                                   class="sr-only peer" {{ old('payment_method') === 'cheque' ? 'checked' : '' }}>
                            <div class="flex flex-col items-center gap-2 p-3 border-2 rounded-lg transition
                                        peer-checked:border-primary peer-checked:bg-primary/5 border-gray-200 hover:border-gray-300">
                                <i class="fa fa-file-alt text-xl text-gray-500"></i>
                                <span class="text-sm font-medium text-gray-700">شيك</span>
                            </div>
                        </label>
                    </div>

                    {{-- حقل رقم الشيك --}}
                    <div x-show="paymentMethod === 'cheque'" x-transition class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الشيك</label>
                            <input type="text" name="cheque_number" value="{{ old('cheque_number') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">المرجع البنكي</label>
                            <input type="text" name="bank_reference" value="{{ old('bank_reference') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
                        </div>
                    </div>

                    {{-- حقل المرجع البنكي للتحويل --}}
                    <div x-show="paymentMethod === 'bank'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">رقم المرجع / التحويل</label>
                        <input type="text" name="bank_reference" value="{{ old('bank_reference') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
                    </div>
                </div>

                {{-- ملاحظات --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <textarea name="notes" rows="3" placeholder="ملاحظات إضافية..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- الشريط الجانبي --}}
            <div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sticky top-6">
                    <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center
                        {{ $type === 'receipt' ? 'bg-green-100' : 'bg-red-100' }}">
                        <i class="fa fa-{{ $type === 'receipt' ? 'arrow-down text-green-600' : 'arrow-up text-red-600' }} text-2xl"></i>
                    </div>
                    <h3 class="text-center font-bold text-gray-800 mb-1">
                        {{ $type === 'receipt' ? 'سند قبض' : 'سند صرف' }}
                    </h3>
                    <p class="text-center text-xs text-gray-500 mb-5">
                        {{ $type === 'receipt' ? 'سيُنشأ قيد محاسبي تلقائياً' : 'سيُنشأ قيد محاسبي تلقائياً' }}
                    </p>

                    <div class="bg-gray-50 rounded-lg p-3 mb-4 text-xs text-gray-600 space-y-1">
                        @if($type === 'receipt')
                        <p class="font-medium text-gray-700">القيد التلقائي:</p>
                        <p>من حـ/الصندوق (مدين)</p>
                        <p>إلى حـ/الحساب المحدد (دائن)</p>
                        @else
                        <p class="font-medium text-gray-700">القيد التلقائي:</p>
                        <p>من حـ/الحساب المحدد (مدين)</p>
                        <p>إلى حـ/الصندوق (دائن)</p>
                        @endif
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 rounded-lg font-medium text-sm text-white transition
                            {{ $type === 'receipt' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                        <i class="fa fa-save ml-1"></i>
                        حفظ السند
                    </button>
                    <a href="{{ route('vouchers.index') }}"
                       class="block w-full text-center mt-2 py-2.5 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">
                        إلغاء
                    </a>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
