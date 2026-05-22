{{-- المسار الكامل: resources/views/pos/sessions/show.blade.php --}}
@extends('layouts.app')

@section('title', 'تفاصيل الجلسة #' . $session->id)

@section('content')
<div class="space-y-6">

    {{-- ── الترويسة ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800">جلسة #{{ $session->id }}</h1>
                @if($session->status === 'open')
                    <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> مفتوحة
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> مغلقة
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-1">
                الكاشير: <strong>{{ $session->user->name ?? '—' }}</strong> |
                فتحت: {{ $session->opened_at->format('Y/m/d h:i A') }} |
                المدة: {{ $session->duration }}
            </p>
        </div>
        <a href="{{ route('pos.sessions.index') }}"
           class="inline-flex items-center gap-2 border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
            ← قائمة الجلسات
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- ── بطاقات الملخص ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
        $cards = [
            ['label' => 'إجمالي المبيعات', 'value' => number_format($summary['total_sales'], 2), 'unit' => 'ج.س', 'color' => 'teal'],
            ['label' => 'نقدي', 'value' => number_format($summary['total_cash'], 2), 'unit' => 'ج.س', 'color' => 'green'],
            ['label' => 'آجل', 'value' => number_format($summary['total_credit'], 2), 'unit' => 'ج.س', 'color' => 'blue'],
            ['label' => 'خصومات ممنوحة', 'value' => number_format($summary['total_discount'], 2), 'unit' => 'ج.س', 'color' => 'orange'],
        ];
        $colorMap = [
            'teal' => 'bg-teal-50 text-teal-700',
            'green' => 'bg-green-50 text-green-700',
            'blue' => 'bg-blue-50 text-blue-700',
            'orange' => 'bg-orange-50 text-orange-700',
        ];
        @endphp
        @foreach($cards as $card)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 mb-1">{{ $card['label'] }}</p>
            <p class="text-xl font-bold {{ Str::before($colorMap[$card['color']], ' ') === 'bg-teal-50' ? 'text-teal-700' : '' }}
               {{ $card['color'] === 'teal' ? 'text-teal-700' : '' }}
               {{ $card['color'] === 'green' ? 'text-green-700' : '' }}
               {{ $card['color'] === 'blue' ? 'text-blue-700' : '' }}
               {{ $card['color'] === 'orange' ? 'text-orange-700' : '' }}">
                {{ $card['value'] }}
                <span class="text-xs font-normal text-gray-400">{{ $card['unit'] }}</span>
            </p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── تفاصيل الصندوق + إغلاق الجلسة ── --}}
        <div class="space-y-4">

            {{-- تفاصيل الصندوق --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">حركة الصندوق</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">رصيد الافتتاح:</span>
                        <span class="font-semibold">{{ number_format($session->opening_balance, 2) }} ج.س</span>
                    </div>
                    <div class="flex justify-between text-green-600">
                        <span>+ المبيعات النقدية:</span>
                        <span class="font-semibold">{{ number_format($summary['total_cash'], 2) }} ج.س</span>
                    </div>
                    @if($session->cash_in > 0)
                    <div class="flex justify-between text-blue-600">
                        <span>+ نقدي مضاف:</span>
                        <span class="font-semibold">{{ number_format($session->cash_in, 2) }} ج.س</span>
                    </div>
                    @endif
                    @if($session->cash_out > 0)
                    <div class="flex justify-between text-red-600">
                        <span>- نقدي مسحوب:</span>
                        <span class="font-semibold">{{ number_format($session->cash_out, 2) }} ج.س</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold border-t border-gray-100 pt-3 text-gray-800">
                        <span>الرصيد المتوقع:</span>
                        <span class="text-primary text-base">{{ number_format($summary['expected_balance'], 2) }} ج.س</span>
                    </div>
                    @if($session->status === 'closed')
                    <div class="flex justify-between font-bold">
                        <span class="text-gray-700">رصيد الإغلاق الفعلي:</span>
                        <span>{{ number_format($session->closing_balance, 2) }} ج.س</span>
                    </div>
                    @php $diff = $summary['difference']; @endphp
                    <div class="flex justify-between font-bold {{ $diff == 0 ? 'text-green-600' : ($diff > 0 ? 'text-blue-600' : 'text-red-600') }}">
                        <span>الفرق:</span>
                        <span>{{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }} ج.س</span>
                    </div>
                    @if($session->closing_notes)
                    <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-600">
                        <strong>ملاحظات الإغلاق:</strong><br>{{ $session->closing_notes }}
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            {{-- إغلاق الجلسة (إن كانت مفتوحة والمستخدم هو صاحبها أو مدير) --}}
            @if($session->status === 'open' && (auth()->id() === $session->user_id || auth()->user()->hasRole('مدير عام')))
            <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5">
                <h3 class="font-bold text-red-600 mb-4 pb-2 border-b border-red-100">إغلاق الجلسة</h3>
                <form method="POST" action="{{ route('pos.sessions.close', $session) }}" id="closeForm">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">رصيد الإغلاق الفعلي (ج.س) <span class="text-red-500">*</span></label>
                            <input type="number" name="closing_balance" step="0.01" min="0"
                                   placeholder="أدخل المبلغ الموجود في الصندوق"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:border-transparent"
                                   required>
                            <p class="text-xs text-gray-400 mt-1">الرصيد المتوقع: {{ number_format($summary['expected_balance'], 2) }} ج.س</p>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">ملاحظات (اختياري)</label>
                            <textarea name="closing_notes" rows="2"
                                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-300 focus:border-transparent resize-none"
                                      placeholder="أي ملاحظات حول الجلسة..."></textarea>
                        </div>
                        <button type="submit"
                                onclick="return confirm('هل أنت متأكد من إغلاق هذه الجلسة؟ لن تتمكن من إعادة فتحها.')"
                                class="w-full bg-red-500 text-white py-2.5 rounded-lg text-sm font-bold hover:bg-red-600 transition">
                            إغلاق الجلسة
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- نقدي داخل/خارج (فقط للجلسات المفتوحة) --}}
            @if($session->status === 'open' && (auth()->id() === $session->user_id || auth()->user()->hasRole('مدير عام')))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5" x-data="{ tab: 'in' }">
                <h3 class="font-bold text-gray-700 mb-3">حركة نقدي</h3>
                <div class="flex gap-2 mb-4">
                    <button @click="tab = 'in'"
                            :class="tab === 'in' ? 'bg-green-500 text-white' : 'border border-gray-200 text-gray-600'"
                            class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition">
                        + إضافة
                    </button>
                    <button @click="tab = 'out'"
                            :class="tab === 'out' ? 'bg-red-500 text-white' : 'border border-gray-200 text-gray-600'"
                            class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition">
                        - سحب
                    </button>
                </div>
                {{-- نموذج الإضافة --}}
                <form x-show="tab === 'in'" method="POST"
                      action="{{ route('pos.sessions.cash-in', $session) }}" id="cashInForm">
                    @csrf
                    <div class="space-y-2">
                        <input type="number" name="amount" step="0.01" min="0.01" placeholder="المبلغ"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" required>
                        <input type="text" name="reason" placeholder="السبب"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" required>
                        <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-lg text-sm font-semibold">
                            تأكيد الإضافة
                        </button>
                    </div>
                </form>
                {{-- نموذج السحب --}}
                <form x-show="tab === 'out'" method="POST"
                      action="{{ route('pos.sessions.cash-out', $session) }}" id="cashOutForm">
                    @csrf
                    <div class="space-y-2">
                        <input type="number" name="amount" step="0.01" min="0.01" placeholder="المبلغ"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" required>
                        <input type="text" name="reason" placeholder="السبب"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" required>
                        <button type="submit" class="w-full bg-red-500 text-white py-2 rounded-lg text-sm font-semibold">
                            تأكيد السحب
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>{{-- end sidebar --}}

        {{-- ── قائمة المعاملات ── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-700">
                        المعاملات
                        <span class="text-xs text-gray-400 font-normal mr-1">({{ $session->transactions->count() }})</span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-right text-xs text-gray-500">
                                <th class="px-4 py-2.5 font-medium">الإيصال</th>
                                <th class="px-4 py-2.5 font-medium">العميل</th>
                                <th class="px-4 py-2.5 font-medium">الوقت</th>
                                <th class="px-4 py-2.5 font-medium text-center">أصناف</th>
                                <th class="px-4 py-2.5 font-medium">الإجمالي</th>
                                <th class="px-4 py-2.5 font-medium text-center">الدفع</th>
                                <th class="px-4 py-2.5 font-medium text-center">الحالة</th>
                                <th class="px-4 py-2.5 font-medium text-center">طباعة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($session->transactions as $tx)
                            <tr class="hover:bg-gray-50 transition {{ $tx->status === 'cancelled' ? 'opacity-50' : '' }}">
                                <td class="px-4 py-3 font-mono text-xs text-primary font-bold">
                                    {{ $tx->receipt_number }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 text-xs">
                                    {{ $tx->customer->name ?? 'نقدي' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">
                                    {{ $tx->created_at->format('h:i A') }}
                                </td>
                                <td class="px-4 py-3 text-center font-medium">
                                    {{ $tx->items->count() }}
                                </td>
                                <td class="px-4 py-3 font-bold text-gray-800">
                                    {{ number_format($tx->total, 2) }}
                                    <span class="text-xs font-normal text-gray-400">ج.س</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                        {{ $tx->payment_type === 'cash' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $tx->payment_type === 'credit' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $tx->payment_type === 'split' ? 'bg-purple-100 text-purple-700' : '' }}">
                                        {{ $tx->payment_type_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                        {{ $tx->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $tx->status === 'cancelled' ? 'bg-red-100 text-red-600' : '' }}
                                        {{ $tx->status === 'held' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                        {{ $tx->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($tx->status === 'completed')
                                    <a href="{{ route('pos.receipt', $tx) }}" target="_blank"
                                       class="text-gray-400 hover:text-primary transition" title="طباعة الإيصال">
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>
                                    @else
                                    <span class="text-gray-200">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    لا توجد معاملات في هذه الجلسة
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($session->transactions->where('status','completed')->count() > 0)
                        <tfoot>
                            <tr class="bg-primary text-white text-sm">
                                <td colspan="4" class="px-4 py-3 font-bold">الإجماليات</td>
                                <td class="px-4 py-3 font-bold">
                                    {{ number_format($session->transactions->where('status','completed')->sum('total'), 2) }} ج.س
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- end grid --}}
</div>
@endsection

@push('scripts')
<script>
// AJAX للـ cash-in / cash-out بدلاً من إعادة تحميل الصفحة
document.querySelectorAll('#cashInForm, #cashOutForm').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.textContent = 'جارٍ...';
        try {
            const resp = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(new FormData(this)),
            });
            const data = await resp.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'حدث خطأ');
                btn.disabled = false;
                btn.textContent = 'تأكيد';
            }
        } catch (err) {
            alert('تعذّر الاتصال بالخادم');
            btn.disabled = false;
        }
    });
});
</script>
@endpush
