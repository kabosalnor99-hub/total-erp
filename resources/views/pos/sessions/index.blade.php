{{-- المسار الكامل: resources/views/pos/sessions/index.blade.php --}}
@extends('layouts.app')

@section('title', 'جلسات نقطة البيع')

@section('content')
<div class="space-y-6">

    {{-- ── الترويسة ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">جلسات نقطة البيع</h1>
            <p class="text-sm text-gray-500 mt-1">سجل كامل بجلسات الكاشير وحركات الصندوق</p>
        </div>
        <a href="{{ route('pos.index') }}"
           class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-primary-dark transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            الذهاب للكاشير
        </a>
    </div>

    {{-- ── إحصائيات اليوم ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-teal-50 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">مبيعات اليوم</p>
                <p class="text-xl font-bold text-gray-800">{{ number_format($stats['today_sales'], 2) }} <span class="text-sm font-normal">ج.س</span></p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">جلسات مفتوحة</p>
                <p class="text-xl font-bold text-gray-800">{{ $stats['open_sessions'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">معاملات اليوم</p>
                <p class="text-xl font-bold text-gray-800">{{ number_format($stats['total_today'], 2) }} <span class="text-sm font-normal">ج.س</span></p>
            </div>
        </div>
    </div>

    {{-- ── الفلاتر ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <form method="GET" action="{{ route('pos.sessions.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">من تاريخ</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">إلى تاريخ</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">الحالة</label>
                <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">الكل</option>
                    <option value="open"   {{ request('status') === 'open'   ? 'selected' : '' }}>مفتوحة</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>مغلقة</option>
                </select>
            </div>
            <button type="submit"
                    class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-primary-dark transition">
                بحث
            </button>
            @if(request()->hasAny(['from','to','status','user_id']))
            <a href="{{ route('pos.sessions.index') }}"
               class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                مسح
            </a>
            @endif
        </form>
    </div>

    {{-- ── الجدول ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-primary text-white text-right">
                        <th class="px-4 py-3 font-semibold">#</th>
                        <th class="px-4 py-3 font-semibold">الكاشير</th>
                        <th class="px-4 py-3 font-semibold">وقت الفتح</th>
                        <th class="px-4 py-3 font-semibold">وقت الإغلاق</th>
                        <th class="px-4 py-3 font-semibold text-center">المعاملات</th>
                        <th class="px-4 py-3 font-semibold">إجمالي المبيعات</th>
                        <th class="px-4 py-3 font-semibold">نقدي</th>
                        <th class="px-4 py-3 font-semibold">آجل</th>
                        <th class="px-4 py-3 font-semibold text-center">الحالة</th>
                        <th class="px-4 py-3 font-semibold text-center">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions as $session)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">#{{ $session->id }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-primary rounded-full flex items-center justify-center text-white text-xs font-bold">
                                    {{ mb_substr($session->user->name ?? '?', 0, 1) }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $session->user->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            {{ $session->opened_at->format('Y/m/d') }}<br>
                            <span class="text-gray-400">{{ $session->opened_at->format('h:i A') }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            @if($session->closed_at)
                                {{ $session->closed_at->format('Y/m/d') }}<br>
                                <span class="text-gray-400">{{ $session->closed_at->format('h:i A') }}</span>
                            @else
                                <span class="text-green-500 font-medium">مفتوحة الآن</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-gray-700">
                            {{ $session->transactions_count }}
                        </td>
                        <td class="px-4 py-3 font-bold text-gray-800">
                            {{ number_format($session->total_sales, 2) }}
                            <span class="text-xs font-normal text-gray-400">ج.س</span>
                        </td>
                        <td class="px-4 py-3 text-green-700 font-medium">
                            {{ number_format($session->total_cash, 2) }}
                        </td>
                        <td class="px-4 py-3 text-blue-700 font-medium">
                            {{ number_format($session->total_credit, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($session->status === 'open')
                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                    مفتوحة
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    مغلقة
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('pos.sessions.show', $session) }}"
                               class="inline-flex items-center gap-1 text-primary hover:text-primary-dark text-xs font-semibold hover:underline transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                تفاصيل
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                <svg class="w-12 h-12 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="font-medium">لا توجد جلسات</p>
                                <p class="text-sm">لم يتم العثور على جلسات بهذه المعايير</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($sessions->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
