{{-- المسار الكامل: resources/views/pos/report.blade.php --}}
@extends('layouts.app')

@section('title', 'تقرير نقطة البيع — ' . \Carbon\Carbon::parse($date)->format('Y/m/d'))

@section('content')
<div class="space-y-6" x-data="{ printMode: false }">

    {{-- ── الترويسة ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">تقرير نقطة البيع</h1>
            <p class="text-sm text-gray-500 mt-1">
                اليوم: <strong>{{ \Carbon\Carbon::parse($date)->format('l، d/m/Y') }}</strong>
                @if($userId)
                    | الكاشير:
                    <strong>{{ \App\Models\User::find($userId)?->name ?? '—' }}</strong>
                @endif
            </p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-primary-dark transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                طباعة
            </button>
            <a href="{{ route('pos.sessions.index') }}"
               class="inline-flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                الجلسات
            </a>
        </div>
    </div>

    {{-- ── فلاتر ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 no-print">
        <form method="GET" action="{{ route('pos.report') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">التاريخ</label>
                <input type="date" name="date" value="{{ $date }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">الكاشير</label>
                <select name="user_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">الكل</option>
                    @foreach($cashiers as $cashier)
                    <option value="{{ $cashier->id }}" {{ $userId == $cashier->id ? 'selected' : '' }}>
                        {{ $cashier->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-primary-dark transition">
                عرض التقرير
            </button>
        </form>
    </div>

    {{-- ── رأس التقرير للطباعة فقط ── --}}
    <div class="print-only hidden text-center mb-4">
        <h2 class="text-xl font-bold">توتال الكلاكلة — تقرير نقطة البيع</h2>
        <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($date)->format('l، d/m/Y') }}</p>
        <hr class="my-2">
    </div>

    {{-- ── بطاقات الملخص ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        @php
        $statCards = [
            ['label' => 'إجمالي المبيعات', 'value' => number_format($summary['total_sales'], 2), 'suffix' => 'ج.س', 'icon' => '💰', 'bg' => 'bg-teal-50', 'text' => 'text-teal-700'],
            ['label' => 'نقدي', 'value' => number_format($summary['total_cash'], 2), 'suffix' => 'ج.س', 'icon' => '💵', 'bg' => 'bg-green-50', 'text' => 'text-green-700'],
            ['label' => 'آجل', 'value' => number_format($summary['total_credit'], 2), 'suffix' => 'ج.س', 'icon' => '📋', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
            ['label' => 'خصومات', 'value' => number_format($summary['total_discount'], 2), 'suffix' => 'ج.س', 'icon' => '🏷️', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700'],
            ['label' => 'عدد المعاملات', 'value' => $summary['transactions_count'], 'suffix' => '', 'icon' => '🧾', 'bg' => 'bg-purple-50', 'text' => 'text-purple-700'],
        ];
        @endphp
        @foreach($statCards as $card)
        <div class="{{ $card['bg'] }} rounded-xl p-4 border border-white">
            <div class="text-2xl mb-1">{{ $card['icon'] }}</div>
            <p class="text-xs text-gray-500 mb-1">{{ $card['label'] }}</p>
            <p class="text-lg font-bold {{ $card['text'] }}">
                {{ $card['value'] }}
                @if($card['suffix'])
                <span class="text-xs font-normal text-gray-400">{{ $card['suffix'] }}</span>
                @endif
            </p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── الأكثر مبيعاً ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-700">أكثر المنتجات مبيعاً</h3>
                <p class="text-xs text-gray-400 mt-0.5">أعلى 10 أصناف ليوم {{ $date }}</p>
            </div>
            <div class="p-4">
                @forelse($summary['top_products'] as $i => $product)
                <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                        {{ $i === 0 ? 'bg-yellow-100 text-yellow-700' : ($i === 1 ? 'bg-gray-100 text-gray-600' : ($i === 2 ? 'bg-orange-100 text-orange-600' : 'bg-gray-50 text-gray-400')) }}">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-700 truncate">{{ $product->name_ar }}</p>
                        <p class="text-xs text-gray-400">{{ number_format($product->qty_sold, 0) }} قطعة</p>
                    </div>
                    <div class="text-left flex-shrink-0">
                        <p class="text-sm font-bold text-primary">{{ number_format($product->revenue, 2) }}</p>
                        <p class="text-xs text-gray-400">ج.س</p>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-gray-400">
                    <p class="text-sm">لا توجد مبيعات في هذا اليوم</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ── توزيع طرق الدفع (نص) ── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- توزيع طرق الدفع --}}
            @if($transactions->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-700 mb-4">توزيع طرق الدفع</h3>
                <div class="grid grid-cols-3 gap-4 text-center">
                    @php
                    $cashCount   = $transactions->where('payment_type', 'cash')->count();
                    $creditCount = $transactions->where('payment_type', 'credit')->count();
                    $splitCount  = $transactions->where('payment_type', 'split')->count();
                    $total       = $transactions->count();
                    @endphp
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-2xl font-bold text-green-700">{{ $cashCount }}</p>
                        <p class="text-xs text-gray-500 mt-1">نقدي</p>
                        <p class="text-xs text-green-600 font-medium mt-1">
                            {{ $total > 0 ? round($cashCount / $total * 100) : 0 }}%
                        </p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-2xl font-bold text-blue-700">{{ $creditCount }}</p>
                        <p class="text-xs text-gray-500 mt-1">آجل</p>
                        <p class="text-xs text-blue-600 font-medium mt-1">
                            {{ $total > 0 ? round($creditCount / $total * 100) : 0 }}%
                        </p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <p class="text-2xl font-bold text-purple-700">{{ $splitCount }}</p>
                        <p class="text-xs text-gray-500 mt-1">مختلط</p>
                        <p class="text-xs text-purple-600 font-medium mt-1">
                            {{ $total > 0 ? round($splitCount / $total * 100) : 0 }}%
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- ملخص حسب الكاشير --}}
            @if(!$userId && $cashiers->count() > 1)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-700">ملخص حسب الكاشير</h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-right text-xs text-gray-500">
                            <th class="px-4 py-2.5 font-medium">الكاشير</th>
                            <th class="px-4 py-2.5 font-medium text-center">معاملات</th>
                            <th class="px-4 py-2.5 font-medium">إجمالي</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($cashiers as $cashier)
                        @php
                        $cashierTx = $transactions->where('user_id', $cashier->id);
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $cashier->name }}</td>
                            <td class="px-4 py-3 text-center">{{ $cashierTx->count() }}</td>
                            <td class="px-4 py-3 font-bold text-primary">
                                {{ number_format($cashierTx->sum('total'), 2) }} ج.س
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
    </div>

    {{-- ── جدول المعاملات التفصيلي ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-700">
                تفاصيل المعاملات
                <span class="text-xs text-gray-400 font-normal mr-1">({{ $transactions->count() }} معاملة)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-primary text-white text-right text-xs">
                        <th class="px-4 py-3 font-semibold">رقم الإيصال</th>
                        <th class="px-4 py-3 font-semibold">الوقت</th>
                        <th class="px-4 py-3 font-semibold">الكاشير</th>
                        <th class="px-4 py-3 font-semibold">العميل</th>
                        <th class="px-4 py-3 font-semibold text-center">أصناف</th>
                        <th class="px-4 py-3 font-semibold">الإجمالي</th>
                        <th class="px-4 py-3 font-semibold">الخصم</th>
                        <th class="px-4 py-3 font-semibold text-center">الدفع</th>
                        <th class="px-4 py-3 font-semibold no-print text-center">إيصال</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-xs font-bold text-primary">
                            {{ $tx->receipt_number }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $tx->created_at->format('h:i A') }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 text-xs">
                            {{ $tx->user->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 text-xs">
                            {{ $tx->customer->name ?? 'نقدي' }}
                        </td>
                        <td class="px-4 py-3 text-center font-medium text-gray-700">
                            {{ $tx->items->count() }}
                        </td>
                        <td class="px-4 py-3 font-bold text-gray-800">
                            {{ number_format($tx->total, 2) }}
                            <span class="text-xs font-normal text-gray-400">ج.س</span>
                        </td>
                        <td class="px-4 py-3 text-orange-600 font-medium text-xs">
                            {{ $tx->discount_amount > 0 ? number_format($tx->discount_amount, 2) . ' ج.س' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $tx->payment_type === 'cash'   ? 'bg-green-100 text-green-700' : '' }}
                                {{ $tx->payment_type === 'credit' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $tx->payment_type === 'split'  ? 'bg-purple-100 text-purple-700' : '' }}">
                                {{ $tx->payment_type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center no-print">
                            <a href="{{ route('pos.receipt', $tx) }}" target="_blank"
                               class="text-gray-400 hover:text-primary transition" title="طباعة">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                </svg>
                                <p class="font-medium">لا توجد مبيعات في هذا اليوم</p>
                                <p class="text-sm">جرّب تغيير التاريخ أو الكاشير</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($transactions->count() > 0)
                <tfoot>
                    <tr class="bg-gray-800 text-white text-sm">
                        <td colspan="5" class="px-4 py-3 font-bold">الإجمالي العام</td>
                        <td class="px-4 py-3 font-bold text-lg">
                            {{ number_format($summary['total_sales'], 2) }}
                            <span class="text-xs font-normal">ج.س</span>
                        </td>
                        <td class="px-4 py-3 font-bold text-orange-300">
                            {{ number_format($summary['total_discount'], 2) }} ج.س
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- تذييل الطباعة --}}
    <div class="print-only hidden text-center text-xs text-gray-500 mt-4">
        <hr class="mb-2">
        <p>تم إنشاء هذا التقرير بواسطة نظام ERP — توتال الكلاكلة</p>
        <p>{{ now()->format('Y/m/d h:i A') }}</p>
    </div>

</div>

<style>
@media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    body { background: #fff; }
    .bg-primary { background-color: #00838F !important; -webkit-print-color-adjust: exact; }
}
</style>
@endsection
