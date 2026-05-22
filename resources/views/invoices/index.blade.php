{{-- المسار: resources/views/invoices/index.blade.php --}}
@extends('layouts.app')

@section('title', 'الفواتير')

@section('content')
<div class="space-y-6">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">إدارة الفواتير</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $invoices->total() }} فاتورة</p>
        </div>
        @canPermission('invoices.create')
        <a href="{{ route('invoices.create') }}"
           class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition font-medium">
            <i class="fa fa-plus"></i> فاتورة جديدة
        </a>
        @endcanPermission
    </div>

    {{-- إحصائيات --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 mb-1">مبيعات اليوم</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($stats['today_total'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 mb-1">مبيعات الشهر</p>
            <p class="text-xl font-bold text-primary">{{ number_format($stats['month_total'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 mb-1">فواتير معلقة</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-gray-500 mb-1">متأخرة السداد</p>
            <p class="text-xl font-bold text-red-600">{{ number_format($stats['overdue']) }}</p>
        </div>
    </div>

    {{-- فلاتر --}}
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">بحث</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="رقم الفاتورة أو اسم العميل..."
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الحالة</label>
                <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">الكل</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>مسودة</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>مؤكدة</option>
                    <option value="paid"      {{ request('status') === 'paid'      ? 'selected' : '' }}>مدفوعة</option>
                    <option value="partial"   {{ request('status') === 'partial'   ? 'selected' : '' }}>جزئية</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">النوع</label>
                <select name="type" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">الكل</option>
                    <option value="cash"    {{ request('type') === 'cash'    ? 'selected' : '' }}>نقدي</option>
                    <option value="credit"  {{ request('type') === 'credit'  ? 'selected' : '' }}>آجل</option>
                    <option value="partial" {{ request('type') === 'partial' ? 'selected' : '' }}>جزئي</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">من</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">إلى</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-dark transition">
                    <i class="fa fa-search me-1"></i> بحث
                </button>
                <a href="{{ route('invoices.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    إعادة تعيين
                </a>
                <a href="{{ route('invoices.aging') }}" class="bg-red-50 text-red-600 px-4 py-2 rounded-lg text-sm hover:bg-red-100 transition">
                    <i class="fa fa-clock me-1"></i> المتأخرة
                </a>
            </div>
        </form>
    </div>

    {{-- جدول الفواتير --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">رقم الفاتورة</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">العميل</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">النوع</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الإجمالي</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">المدفوع</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">المتبقي</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الاستحقاق</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">الحالة</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">التاريخ</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50 transition {{ $invoice->is_overdue ? 'bg-red-50/30' : '' }}">
                        <td class="px-4 py-3">
                            <a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-primary hover:underline font-medium text-xs">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $invoice->customer?->name ?? 'عميل نقدي' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-600">{{ $invoice->type_label }}</span>
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ number_format($invoice->total, 2) }}</td>
                        <td class="px-4 py-3 text-green-600">{{ number_format($invoice->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 {{ $invoice->remaining_amount > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                            {{ $invoice->remaining_amount > 0 ? number_format($invoice->remaining_amount, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs {{ $invoice->is_overdue ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                            {{ $invoice->due_date?->format('Y/m/d') ?? '—' }}
                            @if($invoice->is_overdue) <i class="fa fa-circle-exclamation ms-1"></i> @endif
                        </td>
                        <td class="px-4 py-3">
                            @php $c = $invoice->status_color; @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $c }}-100 text-{{ $c }}-700">
                                {{ $invoice->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $invoice->created_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('invoices.show', $invoice) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="عرض">
                                    <i class="fa fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition" title="طباعة">
                                    <i class="fa fa-print text-xs"></i>
                                </a>
                                <a href="{{ route('invoices.pdf', $invoice) }}" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition" title="PDF">
                                    <i class="fa fa-file-pdf text-xs"></i>
                                </a>
                                @if(in_array($invoice->status, ['confirmed','partial']))
                                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}"
                                   class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="إضافة دفعة">
                                    <i class="fa fa-money-bill text-xs"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                            <i class="fa fa-receipt text-4xl mb-3 block opacity-30"></i>
                            لا توجد فواتير
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
