{{-- المسار الكامل: resources/views/vouchers/index.blade.php --}}
@extends('layouts.app')

@section('title', 'السندات المالية')

@section('content')
<div class="p-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">السندات المالية</h1>
            <p class="text-sm text-gray-500 mt-1">سندات القبض والصرف</p>
        </div>
        <div class="flex gap-2">
            @canPermission('vouchers.create')
            <a href="{{ route('vouchers.create', ['type' => 'receipt']) }}"
               class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                <i class="fa fa-arrow-down"></i>
                سند قبض
            </a>
            <a href="{{ route('vouchers.create', ['type' => 'payment']) }}"
               class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
                <i class="fa fa-arrow-up"></i>
                سند صرف
            </a>
            @endcanPermission
        </div>
    </div>

    {{-- بطاقات الإحصاء --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fa fa-arrow-down text-green-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">سندات القبض</p>
                <p class="text-lg font-bold text-gray-800">{{ number_format($stats['count_receipts']) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center">
                <i class="fa fa-dollar-sign text-green-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">إجمالي القبض</p>
                <p class="text-lg font-bold text-green-600">{{ number_format($stats['total_receipts'], 2) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fa fa-arrow-up text-red-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">سندات الصرف</p>
                <p class="text-lg font-bold text-gray-800">{{ number_format($stats['count_payments']) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center">
                <i class="fa fa-dollar-sign text-red-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">إجمالي الصرف</p>
                <p class="text-lg font-bold text-red-600">{{ number_format($stats['total_payments'], 2) }}</p>
            </div>
        </div>
    </div>

    {{-- فلاتر البحث --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <form method="GET" action="{{ route('vouchers.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="min-w-[140px]">
                <label class="block text-xs text-gray-500 mb-1">النوع</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
                    <option value="">الكل</option>
                    <option value="receipt" {{ request('type') === 'receipt' ? 'selected' : '' }}>سندات القبض</option>
                    <option value="payment" {{ request('type') === 'payment' ? 'selected' : '' }}>سندات الصرف</option>
                </select>
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-gray-500 mb-1">بحث</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="رقم السند أو الوصف..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
            </div>
            <div class="min-w-[130px]">
                <label class="block text-xs text-gray-500 mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
            </div>
            <div class="min-w-[130px]">
                <label class="block text-xs text-gray-500 mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-dark transition">
                    <i class="fa fa-search ml-1"></i> بحث
                </button>
                <a href="{{ route('vouchers.index') }}"
                   class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    مسح
                </a>
            </div>
        </form>
    </div>

    {{-- الجدول --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary text-white">
                    <th class="px-4 py-3 text-right font-medium">رقم السند</th>
                    <th class="px-4 py-3 text-right font-medium">النوع</th>
                    <th class="px-4 py-3 text-right font-medium">التاريخ</th>
                    <th class="px-4 py-3 text-right font-medium">الحساب</th>
                    <th class="px-4 py-3 text-right font-medium">البيان</th>
                    <th class="px-4 py-3 text-center font-medium">طريقة الدفع</th>
                    <th class="px-4 py-3 text-center font-medium">المبلغ</th>
                    <th class="px-4 py-3 text-center font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($vouchers as $voucher)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('vouchers.show', $voucher) }}"
                           class="font-mono text-primary hover:underline font-semibold text-xs">
                            {{ $voucher->voucher_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $voucher->type === 'receipt' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            <i class="fa fa-{{ $voucher->type === 'receipt' ? 'arrow-down' : 'arrow-up' }} ml-1"></i>
                            {{ $voucher->type_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ $voucher->date->format('Y/m/d') }}
                    </td>
                    <td class="px-4 py-3 text-gray-700 text-xs">
                        {{ $voucher->account->name_ar ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate">
                        {{ $voucher->description }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">
                            {{ $voucher->payment_method_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center font-mono font-bold
                        {{ $voucher->type === 'receipt' ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($voucher->amount, 2) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('vouchers.show', $voucher) }}"
                               class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="عرض">
                                <i class="fa fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('vouchers.print', $voucher) }}" target="_blank"
                               class="p-1.5 text-gray-600 hover:bg-gray-100 rounded transition" title="طباعة">
                                <i class="fa fa-print text-xs"></i>
                            </a>
                            @canPermission('vouchers.delete')
                            <form method="POST" action="{{ route('vouchers.destroy', $voucher) }}"
                                  onsubmit="return confirm('حذف هذا السند؟')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded transition" title="حذف">
                                    <i class="fa fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endcanPermission
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <i class="fa fa-file-invoice text-4xl mb-3 block opacity-30"></i>
                        لا توجد سندات
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($vouchers->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $vouchers->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
