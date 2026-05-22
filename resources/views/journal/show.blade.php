{{-- المسار الكامل: resources/views/journal/show.blade.php --}}
@extends('layouts.app')

@section('title', 'قيد محاسبي — ' . $journalEntry->entry_number)

@section('content')
<div class="p-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                قيد رقم: <span class="text-primary font-mono">{{ $journalEntry->entry_number }}</span>
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                أُنشئ بواسطة {{ $journalEntry->user->name ?? '—' }}
                في {{ $journalEntry->created_at->format('Y/m/d H:i') }}
            </p>
        </div>
        <div class="flex gap-2">
            @if($journalEntry->status === 'draft')
            @canPermission('journal.post')
            <form method="POST" action="{{ route('journal.post', $journalEntry) }}"
                  onsubmit="return confirm('ترحيل هذا القيد؟ لن تتمكن من تعديله بعد الترحيل.')">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                    <i class="fa fa-check"></i> ترحيل القيد
                </button>
            </form>
            @endcanPermission
            @canPermission('journal.delete')
            <form method="POST" action="{{ route('journal.destroy', $journalEntry) }}"
                  onsubmit="return confirm('حذف هذا القيد نهائياً؟')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm">
                    <i class="fa fa-trash"></i> حذف
                </button>
            </form>
            @endcanPermission
            @elseif($journalEntry->status === 'posted')
            @canPermission('journal.unpost')
            <form method="POST" action="{{ route('journal.unpost', $journalEntry) }}"
                  onsubmit="return confirm('إلغاء ترحيل هذا القيد؟')">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition text-sm">
                    <i class="fa fa-undo"></i> إلغاء الترحيل
                </button>
            </form>
            @endcanPermission
            @endif
            <button onclick="window.print()"
                    class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                <i class="fa fa-print"></i> طباعة
            </button>
            <a href="{{ route('journal.index') }}"
               class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                <i class="fa fa-arrow-right"></i> العودة
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- تفاصيل القيد --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- بيانات القيد --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa fa-info-circle text-primary"></i>
                    بيانات القيد
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">رقم القيد</p>
                        <p class="font-bold font-mono text-primary">{{ $journalEntry->entry_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">التاريخ</p>
                        <p class="font-medium">{{ $journalEntry->date->format('Y/m/d') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">الحالة</p>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $journalEntry->status === 'posted' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $journalEntry->status_label }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">المصدر</p>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">
                            {{ $journalEntry->source_label }}
                        </span>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-400 mb-1">البيان</p>
                        <p class="font-medium text-gray-800">{{ $journalEntry->description }}</p>
                    </div>
                    @if($journalEntry->notes)
                    <div class="col-span-3">
                        <p class="text-xs text-gray-400 mb-1">ملاحظات</p>
                        <p class="text-gray-600">{{ $journalEntry->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- سطور القيد --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fa fa-list text-primary"></i>
                        سطور القيد
                    </h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs">
                            <th class="px-4 py-3 text-right font-medium">#</th>
                            <th class="px-4 py-3 text-right font-medium">الحساب</th>
                            <th class="px-4 py-3 text-right font-medium">البيان</th>
                            <th class="px-4 py-3 text-center font-medium">مدين</th>
                            <th class="px-4 py-3 text-center font-medium">دائن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($journalEntry->lines as $line)
                        <tr class="{{ $line->debit > 0 ? '' : 'bg-gray-50/50' }}">
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">
                                <div class="font-mono text-xs text-gray-400">{{ $line->account->code }}</div>
                                <div class="font-medium text-gray-800">{{ $line->account->name_ar }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $line->description ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($line->debit > 0)
                                <span class="font-mono font-semibold text-gray-800">
                                    {{ number_format($line->debit, 2) }}
                                </span>
                                @else
                                <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($line->credit > 0)
                                <span class="font-mono font-semibold text-gray-800">
                                    {{ number_format($line->credit, 2) }}
                                </span>
                                @else
                                <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary/5 font-bold">
                            <td colspan="3" class="px-4 py-3 text-left text-gray-700 text-sm">الإجمالي</td>
                            <td class="px-4 py-3 text-center font-mono text-gray-900">
                                {{ number_format($journalEntry->total_debit, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-gray-900">
                                {{ number_format($journalEntry->total_credit, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                {{-- مؤشر التوازن --}}
                <div class="px-5 py-3 border-t border-gray-100">
                    @if($journalEntry->is_balanced)
                    <span class="flex items-center gap-2 text-green-700 text-sm">
                        <i class="fa fa-check-circle"></i> القيد متوازن
                    </span>
                    @else
                    <span class="flex items-center gap-2 text-red-600 text-sm">
                        <i class="fa fa-exclamation-circle"></i>
                        القيد غير متوازن — الفرق:
                        {{ number_format(abs($journalEntry->total_debit - $journalEntry->total_credit), 2) }}
                    </span>
                    @endif
                </div>
            </div>

        </div>

        {{-- الشريط الجانبي --}}
        <div class="space-y-5">

            {{-- ملخص مالي --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fa fa-chart-pie text-primary"></i>
                    ملخص مالي
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-500">إجمالي المدين</span>
                        <span class="font-bold font-mono text-gray-800">
                            {{ number_format($journalEntry->total_debit, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-500">إجمالي الدائن</span>
                        <span class="font-bold font-mono text-gray-800">
                            {{ number_format($journalEntry->total_credit, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-500">عدد السطور</span>
                        <span class="font-bold text-gray-800">{{ $journalEntry->lines->count() }}</span>
                    </div>
                </div>
            </div>

            {{-- معلومات المرجع --}}
            @if($journalEntry->reference_type)
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <h3 class="font-semibold text-blue-800 mb-2 text-sm flex items-center gap-2">
                    <i class="fa fa-link"></i>
                    المرجع
                </h3>
                <p class="text-xs text-blue-700">
                    {{ class_basename($journalEntry->reference_type) }}
                    #{{ $journalEntry->reference_id }}
                </p>
            </div>
            @endif

            {{-- تاريخ الإنشاء --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm">معلومات الإنشاء</h3>
                <div class="space-y-2 text-xs text-gray-500">
                    <div class="flex justify-between">
                        <span>المستخدم</span>
                        <span class="text-gray-700 font-medium">{{ $journalEntry->user->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>تاريخ الإنشاء</span>
                        <span class="text-gray-700">{{ $journalEntry->created_at->format('Y/m/d') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>آخر تعديل</span>
                        <span class="text-gray-700">{{ $journalEntry->updated_at->format('Y/m/d') }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<style>
@media print {
    nav, header, .no-print, button, a { display: none !important; }
    body { font-size: 12px; }
}
</style>
@endsection
