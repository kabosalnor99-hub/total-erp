{{-- المسار الكامل: resources/views/vouchers/show.blade.php --}}
@extends('layouts.app')

@section('title', $voucher->type_label . ' — ' . $voucher->voucher_number)

@section('content')
<div class="p-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                {{ $voucher->type_label }}
                <span class="text-primary font-mono">{{ $voucher->voucher_number }}</span>
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                أُنشئ بواسطة {{ $voucher->user->name ?? '—' }}
                في {{ $voucher->created_at->format('Y/m/d H:i') }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('vouchers.print', $voucher) }}" target="_blank"
               class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition text-sm">
                <i class="fa fa-print"></i> طباعة
            </a>
            @canPermission('vouchers.delete')
            <form method="POST" action="{{ route('vouchers.destroy', $voucher) }}"
                  onsubmit="return confirm('حذف هذا السند؟')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
                    <i class="fa fa-trash"></i> حذف
                </button>
            </form>
            @endcanPermission
            <a href="{{ route('vouchers.index') }}"
               class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                <i class="fa fa-arrow-right"></i> العودة
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- تفاصيل السند --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- البطاقة الرئيسية --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                {{-- شريط لون حسب النوع --}}
                <div class="h-2 {{ $voucher->type === 'receipt' ? 'bg-green-500' : 'bg-red-500' }}"></div>

                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-full flex items-center justify-center
                                {{ $voucher->type === 'receipt' ? 'bg-green-100' : 'bg-red-100' }}">
                                <i class="fa fa-{{ $voucher->type === 'receipt' ? 'arrow-down text-green-600' : 'arrow-up text-red-600' }} text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">{{ $voucher->type_label }}</h3>
                                <p class="text-sm text-gray-500">{{ $voucher->voucher_number }}</p>
                            </div>
                        </div>
                        <div class="text-left">
                            <p class="text-xs text-gray-400 mb-1">المبلغ</p>
                            <p class="text-3xl font-bold font-mono
                                {{ $voucher->type === 'receipt' ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($voucher->amount, 2) }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">التاريخ</p>
                            <p class="font-semibold text-gray-800">{{ $voucher->date->format('Y/m/d') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">طريقة الدفع</p>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700 font-medium">
                                {{ $voucher->payment_method_label }}
                            </span>
                        </div>
                        @if($voucher->cheque_number)
                        <div>
                            <p class="text-xs text-gray-400 mb-1">رقم الشيك</p>
                            <p class="font-semibold text-gray-800 font-mono">{{ $voucher->cheque_number }}</p>
                        </div>
                        @endif
                        @if($voucher->bank_reference)
                        <div>
                            <p class="text-xs text-gray-400 mb-1">المرجع البنكي</p>
                            <p class="font-semibold text-gray-800 font-mono">{{ $voucher->bank_reference }}</p>
                        </div>
                        @endif
                        <div class="col-span-2">
                            <p class="text-xs text-gray-400 mb-1">البيان</p>
                            <p class="font-medium text-gray-800">{{ $voucher->description }}</p>
                        </div>
                        @if($voucher->notes)
                        <div class="col-span-3">
                            <p class="text-xs text-gray-400 mb-1">ملاحظات</p>
                            <p class="text-gray-600">{{ $voucher->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- الحسابات المرتبطة --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa fa-exchange-alt text-primary"></i>
                    الحسابات المرتبطة
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-400 mb-2">
                            {{ $voucher->type === 'receipt' ? 'المستلم منه (مدين)' : 'المدفوع عنه (مدين)' }}
                        </p>
                        <p class="font-mono text-xs text-gray-400">{{ $voucher->account->code ?? '—' }}</p>
                        <p class="font-semibold text-gray-800">{{ $voucher->account->name_ar ?? '—' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-400 mb-2">
                            {{ $voucher->type === 'receipt' ? 'الصندوق / البنك (دائن)' : 'الصندوق / البنك (دائن)' }}
                        </p>
                        <p class="font-mono text-xs text-gray-400">{{ $voucher->cashAccount->code ?? '—' }}</p>
                        <p class="font-semibold text-gray-800">{{ $voucher->cashAccount->name_ar ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- القيد المحاسبي المرتبط --}}
            @if($voucher->journalEntry)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fa fa-book text-primary"></i>
                        القيد المحاسبي
                    </h3>
                    <a href="{{ route('journal.show', $voucher->journalEntry) }}"
                       class="text-primary text-sm hover:underline">
                        {{ $voucher->journalEntry->entry_number }}
                        <i class="fa fa-external-link-alt text-xs mr-1"></i>
                    </a>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs">
                            <th class="px-4 py-2 text-right font-medium">الحساب</th>
                            <th class="px-4 py-2 text-center font-medium">مدين</th>
                            <th class="px-4 py-2 text-center font-medium">دائن</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($voucher->journalEntry->lines as $line)
                        <tr class="border-t border-gray-50">
                            <td class="px-4 py-2">
                                <span class="font-mono text-xs text-gray-400">{{ $line->account->code }}</span>
                                <span class="mr-2 text-gray-700">{{ $line->account->name_ar }}</span>
                            </td>
                            <td class="px-4 py-2 text-center font-mono text-xs">
                                {{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}
                            </td>
                            <td class="px-4 py-2 text-center font-mono text-xs">
                                {{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-5 py-2 bg-gray-50 border-t border-gray-100">
                    <span class="text-xs px-2 py-1 rounded-full
                        {{ $voucher->journalEntry->status === 'posted' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $voucher->journalEntry->status_label }}
                    </span>
                </div>
            </div>
            @endif

        </div>

        {{-- الشريط الجانبي --}}
        <div class="space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-4 text-sm">معلومات الإنشاء</h3>
                <div class="space-y-2 text-xs text-gray-500">
                    <div class="flex justify-between">
                        <span>المستخدم</span>
                        <span class="text-gray-700 font-medium">{{ $voucher->user->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>تاريخ الإنشاء</span>
                        <span class="text-gray-700">{{ $voucher->created_at->format('Y/m/d H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
