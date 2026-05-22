{{-- المسار الكامل: resources/views/journal/index.blade.php --}}
@extends('layouts.app')

@section('title', 'القيود المحاسبية')

@section('content')
<div class="p-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">القيود المحاسبية</h1>
            <p class="text-sm text-gray-500 mt-1">إدارة القيود اليدوية والتلقائية</p>
        </div>
        @canPermission('journal.create')
        <a href="{{ route('journal.create') }}"
           class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition text-sm">
            <i class="fa fa-plus"></i>
            قيد جديد
        </a>
        @endcanPermission
    </div>

    {{-- بطاقات الإحصاء --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fa fa-book text-blue-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">إجمالي القيود</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                <i class="fa fa-clock text-yellow-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">مسودة</p>
                <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['draft']) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fa fa-check-circle text-green-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">مُرحَّل</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['posted']) }}</p>
            </div>
        </div>
    </div>

    {{-- فلاتر البحث --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <form method="GET" action="{{ route('journal.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-gray-500 mb-1">بحث</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="رقم القيد أو الوصف..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs text-gray-500 mb-1">الحالة</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
                    <option value="">الكل</option>
                    <option value="draft"  {{ request('status') === 'draft'  ? 'selected' : '' }}>مسودة</option>
                    <option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>مُرحَّل</option>
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs text-gray-500 mb-1">المصدر</label>
                <select name="source" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none">
                    <option value="">الكل</option>
                    <option value="manual"   {{ request('source') === 'manual'   ? 'selected' : '' }}>يدوي</option>
                    <option value="invoice"  {{ request('source') === 'invoice'  ? 'selected' : '' }}>فاتورة بيع</option>
                    <option value="payment"  {{ request('source') === 'payment'  ? 'selected' : '' }}>دفعة عميل</option>
                    <option value="purchase" {{ request('source') === 'purchase' ? 'selected' : '' }}>مشتريات</option>
                    <option value="payroll"  {{ request('source') === 'payroll'  ? 'selected' : '' }}>رواتب</option>
                    <option value="pos"      {{ request('source') === 'pos'      ? 'selected' : '' }}>نقطة البيع</option>
                </select>
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
                <a href="{{ route('journal.index') }}"
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
                    <th class="px-4 py-3 text-right font-medium">رقم القيد</th>
                    <th class="px-4 py-3 text-right font-medium">التاريخ</th>
                    <th class="px-4 py-3 text-right font-medium">الوصف</th>
                    <th class="px-4 py-3 text-right font-medium">المصدر</th>
                    <th class="px-4 py-3 text-center font-medium">إجمالي المدين</th>
                    <th class="px-4 py-3 text-center font-medium">الحالة</th>
                    <th class="px-4 py-3 text-center font-medium">أُنشئ بواسطة</th>
                    <th class="px-4 py-3 text-center font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($entries as $entry)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('journal.show', $entry) }}"
                           class="font-mono text-primary hover:underline font-semibold">
                            {{ $entry->entry_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $entry->date->format('Y/m/d') }}
                    </td>
                    <td class="px-4 py-3 text-gray-700 max-w-xs truncate">
                        {{ $entry->description }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $entry->source === 'manual' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $entry->source_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center font-mono font-semibold text-gray-800">
                        {{ number_format($entry->lines->sum('debit'), 2) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $entry->status === 'posted' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $entry->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500 text-xs">
                        {{ $entry->user->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('journal.show', $entry) }}"
                               class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="عرض">
                                <i class="fa fa-eye text-xs"></i>
                            </a>
                            @if($entry->status === 'draft')
                            @canPermission('journal.post')
                            <form method="POST" action="{{ route('journal.post', $entry) }}"
                                  onsubmit="return confirm('ترحيل القيد {{ $entry->entry_number }}؟')">
                                @csrf
                                <button type="submit"
                                        class="p-1.5 text-green-600 hover:bg-green-50 rounded transition" title="ترحيل">
                                    <i class="fa fa-check text-xs"></i>
                                </button>
                            </form>
                            @endcanPermission
                            @canPermission('journal.delete')
                            <form method="POST" action="{{ route('journal.destroy', $entry) }}"
                                  onsubmit="return confirm('حذف هذا القيد؟')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded transition" title="حذف">
                                    <i class="fa fa-trash text-xs"></i>
                                </button>
                            </form>
                            @endcanPermission
                            @else
                            @canPermission('journal.unpost')
                            <form method="POST" action="{{ route('journal.unpost', $entry) }}"
                                  onsubmit="return confirm('إلغاء ترحيل هذا القيد؟')">
                                @csrf
                                <button type="submit"
                                        class="p-1.5 text-orange-600 hover:bg-orange-50 rounded transition" title="إلغاء ترحيل">
                                    <i class="fa fa-undo text-xs"></i>
                                </button>
                            </form>
                            @endcanPermission
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <i class="fa fa-book text-4xl mb-3 block opacity-30"></i>
                        لا توجد قيود محاسبية
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($entries->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $entries->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
