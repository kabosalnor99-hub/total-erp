{{-- المسار الكامل: resources/views/journal/create.blade.php --}}
@extends('layouts.app')

@section('title', 'قيد محاسبي جديد')

@section('content')
<div class="p-6" x-data="journalForm()">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">قيد محاسبي جديد</h1>
            <p class="text-sm text-gray-500 mt-1">إدخال قيد مزدوج يدوي</p>
        </div>
        <a href="{{ route('journal.index') }}"
           class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
            <i class="fa fa-arrow-right"></i>
            العودة للقائمة
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

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4 text-sm">
        {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('journal.store') }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- العمود الرئيسي --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- بيانات القيد --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fa fa-info-circle text-primary"></i>
                        بيانات القيد
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
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                الوصف / البيان <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="description" value="{{ old('description') }}"
                                   placeholder="مثال: تسوية رصيد نقدي..."
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none"
                                   required>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                            <textarea name="notes" rows="2" placeholder="ملاحظات إضافية..."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- سطور القيد --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fa fa-list text-primary"></i>
                            سطور القيد
                        </h3>
                        <button type="button" @click="addLine()"
                                class="flex items-center gap-1 bg-primary text-white px-3 py-1.5 rounded-lg text-xs hover:bg-primary-dark transition">
                            <i class="fa fa-plus"></i> إضافة سطر
                        </button>
                    </div>

                    {{-- رأس الجدول --}}
                    <div class="grid grid-cols-12 gap-2 mb-2 text-xs font-medium text-gray-500 px-1">
                        <div class="col-span-4">الحساب</div>
                        <div class="col-span-3">البيان</div>
                        <div class="col-span-2 text-center">مدين</div>
                        <div class="col-span-2 text-center">دائن</div>
                        <div class="col-span-1"></div>
                    </div>

                    {{-- سطور القيد الديناميكية --}}
                    <div class="space-y-2">
                        <template x-for="(line, index) in lines" :key="index">
                            <div class="grid grid-cols-12 gap-2 items-center">
                                {{-- اختيار الحساب --}}
                                <div class="col-span-4">
                                    <select :name="`lines[${index}][account_id]`"
                                            x-model="line.account_id"
                                            class="w-full border border-gray-300 rounded-lg px-2 py-2 text-xs focus:ring-2 focus:ring-primary focus:outline-none"
                                            required>
                                        <option value="">اختر الحساب...</option>
                                        @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->code }} — {{ $account->name_ar }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- البيان --}}
                                <div class="col-span-3">
                                    <input type="text" :name="`lines[${index}][description]`"
                                           x-model="line.description"
                                           placeholder="البيان..."
                                           class="w-full border border-gray-300 rounded-lg px-2 py-2 text-xs focus:ring-2 focus:ring-primary focus:outline-none">
                                </div>
                                {{-- المدين --}}
                                <div class="col-span-2">
                                    <input type="number" :name="`lines[${index}][debit]`"
                                           x-model.number="line.debit"
                                           @input="line.credit = line.debit > 0 ? 0 : line.credit; updateTotals()"
                                           min="0" step="0.01" placeholder="0.00"
                                           class="w-full border border-gray-300 rounded-lg px-2 py-2 text-xs text-center focus:ring-2 focus:ring-primary focus:outline-none">
                                </div>
                                {{-- الدائن --}}
                                <div class="col-span-2">
                                    <input type="number" :name="`lines[${index}][credit]`"
                                           x-model.number="line.credit"
                                           @input="line.debit = line.credit > 0 ? 0 : line.debit; updateTotals()"
                                           min="0" step="0.01" placeholder="0.00"
                                           class="w-full border border-gray-300 rounded-lg px-2 py-2 text-xs text-center focus:ring-2 focus:ring-primary focus:outline-none">
                                </div>
                                {{-- حذف --}}
                                <div class="col-span-1 text-center">
                                    <button type="button" @click="removeLine(index)"
                                            x-show="lines.length > 2"
                                            class="text-red-400 hover:text-red-600 transition">
                                        <i class="fa fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- مجموع الأعمدة --}}
                    <div class="grid grid-cols-12 gap-2 mt-3 pt-3 border-t border-gray-200">
                        <div class="col-span-7 text-sm font-semibold text-gray-700 px-1">الإجمالي</div>
                        <div class="col-span-2 text-center">
                            <span class="text-sm font-bold text-gray-800 font-mono"
                                  x-text="totalDebit.toFixed(2)"></span>
                        </div>
                        <div class="col-span-2 text-center">
                            <span class="text-sm font-bold text-gray-800 font-mono"
                                  x-text="totalCredit.toFixed(2)"></span>
                        </div>
                        <div class="col-span-1"></div>
                    </div>

                    {{-- مؤشر التوازن --}}
                    <div class="mt-3">
                        <div x-show="isBalanced && totalDebit > 0"
                             class="flex items-center gap-2 text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2 text-sm">
                            <i class="fa fa-check-circle"></i>
                            <span>القيد متوازن ✓</span>
                        </div>
                        <div x-show="!isBalanced && (totalDebit > 0 || totalCredit > 0)"
                             class="flex items-center gap-2 text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-sm">
                            <i class="fa fa-exclamation-circle"></i>
                            <span>القيد غير متوازن — الفرق:
                                <strong x-text="Math.abs(totalDebit - totalCredit).toFixed(2)"></strong>
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- الشريط الجانبي --}}
            <div class="space-y-5">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sticky top-6">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fa fa-calculator text-primary"></i>
                        ملخص القيد
                    </h3>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">إجمالي المدين</span>
                            <span class="font-bold font-mono text-gray-800" x-text="totalDebit.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">إجمالي الدائن</span>
                            <span class="font-bold font-mono text-gray-800" x-text="totalCredit.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-500">الفرق</span>
                            <span class="font-bold font-mono"
                                  :class="isBalanced ? 'text-green-600' : 'text-red-600'"
                                  x-text="Math.abs(totalDebit - totalCredit).toFixed(2)"></span>
                        </div>
                    </div>

                    <div class="mt-4 p-3 rounded-lg text-center"
                         :class="isBalanced && totalDebit > 0 ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'">
                        <p class="text-xs font-medium"
                           :class="isBalanced && totalDebit > 0 ? 'text-green-700' : 'text-gray-500'"
                           x-text="isBalanced && totalDebit > 0 ? 'القيد جاهز للحفظ' : 'أدخل القيم أولاً'"></p>
                    </div>

                    <div class="mt-5 space-y-2">
                        <button type="submit"
                                :disabled="!isBalanced || totalDebit === 0"
                                :class="isBalanced && totalDebit > 0
                                    ? 'bg-primary hover:bg-primary-dark text-white'
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                                class="w-full py-2.5 rounded-lg font-medium text-sm transition flex items-center justify-center gap-2">
                            <i class="fa fa-save"></i>
                            حفظ كمسودة
                        </button>
                        <a href="{{ route('journal.index') }}"
                           class="block w-full text-center py-2.5 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">
                            إلغاء
                        </a>
                    </div>

                    <p class="text-xs text-gray-400 mt-3 text-center">
                        يُحفظ القيد كمسودة — يمكن ترحيله لاحقاً
                    </p>
                </div>
            </div>

        </div>
    </form>

</div>

@push('scripts')
<script>
function journalForm() {
    return {
        lines: [
            { account_id: '', description: '', debit: 0, credit: 0 },
            { account_id: '', description: '', debit: 0, credit: 0 },
        ],
        totalDebit: 0,
        totalCredit: 0,
        isBalanced: false,

        addLine() {
            this.lines.push({ account_id: '', description: '', debit: 0, credit: 0 });
        },

        removeLine(index) {
            if (this.lines.length > 2) {
                this.lines.splice(index, 1);
                this.updateTotals();
            }
        },

        updateTotals() {
            this.totalDebit  = this.lines.reduce((s, l) => s + (parseFloat(l.debit)  || 0), 0);
            this.totalCredit = this.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0);
            this.isBalanced  = Math.abs(this.totalDebit - this.totalCredit) < 0.01;
        },
    };
}
</script>
@endpush
@endsection
