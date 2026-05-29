{{-- المسار: resources/views/exchange-rates/index.blade.php --}}

@extends('layouts.app')

@section('title', 'سعر صرف الدولار')

@push('styles')
<style>
    .rate-card{background:linear-gradient(135deg,#0d9488 0%,#0f766e 100%);border-radius:1.25rem;color:#fff;padding:2rem}
    .rate-card .value{font-size:3.5rem;font-weight:900;line-height:1;letter-spacing:-1px}
    .rate-card .label{font-size:.85rem;opacity:.8;margin-bottom:.5rem}
    .stat-card{background:#fff;border-radius:1rem;padding:1.25rem 1.5rem;border:1px solid #e5e7eb;transition:.2s}
    .stat-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);transform:translateY(-2px)}
    .chart-container{height:280px;position:relative}
    .history-row:hover{background:#f0fdf9}
    .badge-up{background:#dcfce7;color:#166534;font-size:.75rem;padding:2px 8px;border-radius:999px;font-weight:700}
    .badge-down{background:#fee2e2;color:#991b1b;font-size:.75rem;padding:2px 8px;border-radius:999px;font-weight:700}
    .badge-stable{background:#f3f4f6;color:#6b7280;font-size:.75rem;padding:2px 8px;border-radius:999px;font-weight:700}
    .rate-input{font-size:1.75rem;font-weight:800;text-align:center;border:3px solid #e5e7eb;border-radius:.75rem;padding:.75rem;width:100%;transition:.2s;color:#0d9488}
    .rate-input:focus{border-color:#0d9488;outline:none;box-shadow:0 0 0 4px rgba(13,148,136,.15)}
    .btn-save{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;padding:1rem 2rem;border-radius:.75rem;font-size:1.1rem;font-weight:800;border:none;cursor:pointer;width:100%;transition:.2s;display:flex;align-items:center;justify-content:center;gap:.75rem}
    .btn-save:hover{background:linear-gradient(135deg,#14b8a6,#0d9488);transform:translateY(-2px);box-shadow:0 8px 24px rgba(13,148,136,.35)}
    .btn-save:active{transform:translateY(0)}
    .sdg-preview{background:#f0fdf4;border:2px dashed #86efac;border-radius:.75rem;padding:1rem;text-align:center;transition:.3s}
    .sdg-preview .amount{font-size:2rem;font-weight:800;color:#166534}
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- رأس الصفحة --}}
    <div class="bg-gradient-to-r from-teal-600 to-teal-800 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold flex items-center gap-3">
                    <span class="bg-white/20 rounded-xl p-2 text-2xl">💱</span>
                    إدارة سعر صرف الدولار
                </h1>
                <p class="text-teal-100 text-sm mt-1">تعديل السعر يُحدِّث أسعار جميع المنتجات تلقائياً</p>
            </div>
            <div class="text-left">
                <div class="text-teal-200 text-xs mb-1">عدد المنتجات المتأثرة</div>
                <div class="text-3xl font-black">{{ number_format($productsCount) }}</div>
                <div class="text-teal-200 text-xs">منتج</div>
            </div>
        </div>
    </div>

    {{-- رسائل --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-4 flex items-center gap-3">
        <span class="text-2xl">✅</span>
        <p class="font-medium">{{ session('success') }}</p>
    </div>
    @endif
    @if(session('warning'))
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl px-5 py-4 flex items-center gap-3">
        <span class="text-2xl">⚠️</span>
        <p class="font-medium">{{ session('warning') }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ═══ العمود الأيمن: السعر الحالي + نموذج التحديث ═══ --}}
        <div class="xl:col-span-1 space-y-6">

            {{-- السعر الحالي --}}
            <div class="rate-card shadow-xl">
                <div class="label">🟢 سعر الصرف الحالي</div>
                <div class="value" id="currentRateDisplay">
                    {{ $currentRate ? number_format($currentRate->rate, 2) : '—' }}
                </div>
                <div class="text-white/70 text-sm mt-1">جنيه سوداني مقابل $1</div>
                @if($currentRate)
                <div class="mt-4 pt-4 border-t border-white/20 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <div class="text-white/60 text-xs mb-1">تاريخ التحديث</div>
                        <div class="font-semibold">{{ $currentRate->effective_date->format('Y/m/d') }}</div>
                    </div>
                    <div>
                        <div class="text-white/60 text-xs mb-1">التغيير</div>
                        <div class="font-semibold">
                            @if($currentRate->change_percent > 0)
                                <span class="text-red-300">▲ {{ $currentRate->change_percent }}%</span>
                            @elseif($currentRate->change_percent < 0)
                                <span class="text-green-300">▼ {{ abs($currentRate->change_percent) }}%</span>
                            @else
                                <span class="text-white/60">—</span>
                            @endif
                        </div>
                    </div>
                    @if($currentRate->notes)
                    <div class="col-span-2">
                        <div class="text-white/60 text-xs mb-1">ملاحظات</div>
                        <div class="font-medium text-sm">{{ $currentRate->notes }}</div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- نموذج تحديث السعر --}}
            @if(auth()->user()->hasPermission('settings.edit') || auth()->user()->hasRole('مدير عام'))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <i class="fas fa-edit text-teal-600"></i>
                    تحديث سعر الصرف
                </h3>

                <form method="POST" action="{{ route('exchange-rates.store') }}" id="rateForm">
                    @csrf
                    <div class="space-y-4">

                        {{-- حقل السعر --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                سعر الدولار (جنيه سوداني) <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="rate"
                                   id="newRate"
                                   class="rate-input"
                                   placeholder="{{ $currentRate ? number_format($currentRate->rate, 0) : '0' }}"
                                   step="0.01"
                                   min="1"
                                   required
                                   oninput="updatePreview()">
                            @error('rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- معاينة فورية --}}
                        <div class="sdg-preview" id="sdgPreview">
                            <div class="text-gray-500 text-xs mb-1">$100 تساوي</div>
                            <div class="amount" id="previewAmount">اكتب السعر أعلاه</div>
                            <div class="text-gray-500 text-xs mt-1">جنيه سوداني</div>
                        </div>

                        {{-- تاريخ السريان --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">تاريخ السريان</label>
                            <input type="date"
                                   name="effective_date"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                   value="{{ today()->toDateString() }}"
                                   required>
                        </div>

                        {{-- ملاحظات --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">ملاحظات (اختياري)</label>
                            <input type="text"
                                   name="notes"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                   placeholder="مثال: سعر البنك المركزي">
                        </div>

                        {{-- تحذير --}}
                        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-amber-800 text-sm flex gap-2">
                            <span>⚠️</span>
                            <span>سيتم تحديث أسعار <strong>{{ number_format($productsCount) }} منتج</strong> تلقائياً</span>
                        </div>

                        <button type="submit"
                                class="btn-save"
                                onclick="return confirm('هل أنت متأكد من تحديث سعر الصرف؟\nسيتم تحديث أسعار {{ $productsCount }} منتج تلقائياً.')">
                            <i class="fas fa-save"></i>
                            حفظ وتحديث جميع الأسعار
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>

        {{-- ═══ العمود الأيسر: الإحصائيات + الرسم + السجل ═══ --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- إحصائيات 30 يوم --}}
            @if($stats)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="stat-card">
                    <div class="text-xs text-gray-500 mb-1 flex items-center gap-1">
                        <i class="fas fa-chart-line text-teal-500"></i> أعلى سعر (30 يوم)
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['highest_30d'], 0) }}</div>
                    <div class="text-xs text-gray-400">ج.س/$</div>
                </div>
                <div class="stat-card">
                    <div class="text-xs text-gray-500 mb-1 flex items-center gap-1">
                        <i class="fas fa-chart-line text-blue-500"></i> أدنى سعر (30 يوم)
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['lowest_30d'], 0) }}</div>
                    <div class="text-xs text-gray-400">ج.س/$</div>
                </div>
                <div class="stat-card">
                    <div class="text-xs text-gray-500 mb-1 flex items-center gap-1">
                        <i class="fas fa-equals text-purple-500"></i> متوسط (30 يوم)
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['avg_30d'], 0) }}</div>
                    <div class="text-xs text-gray-400">ج.س/$</div>
                </div>
                <div class="stat-card">
                    <div class="text-xs text-gray-500 mb-1 flex items-center gap-1">
                        <i class="fas fa-sync text-orange-500"></i> عدد التحديثات
                    </div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['updates_count_30d'] }}</div>
                    <div class="text-xs text-gray-400">آخر 30 يوم</div>
                </div>
            </div>
            @endif

            {{-- الرسم البياني --}}
            @if($chartData->count() > 1)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-area text-teal-600"></i>
                    تطور سعر الصرف
                </h3>
                <div class="chart-container">
                    <canvas id="rateChart"></canvas>
                </div>
            </div>
            @endif

            {{-- سجل الأسعار --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-history text-teal-600"></i>
                        سجل أسعار الصرف
                    </h3>
                    <span class="text-sm text-gray-500">{{ $history->total() }} سجل</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-right text-xs font-semibold text-gray-500 px-5 py-3 rounded-r-lg">التاريخ</th>
                                <th class="text-right text-xs font-semibold text-gray-500 px-5 py-3">السعر (ج.س/$)</th>
                                <th class="text-right text-xs font-semibold text-gray-500 px-5 py-3">التغيير</th>
                                <th class="text-right text-xs font-semibold text-gray-500 px-5 py-3">السعر السابق</th>
                                <th class="text-right text-xs font-semibold text-gray-500 px-5 py-3">ملاحظات</th>
                                <th class="text-right text-xs font-semibold text-gray-500 px-5 py-3">بواسطة</th>
                                <th class="text-right text-xs font-semibold text-gray-500 px-5 py-3 rounded-l-lg">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $rate)
                            <tr class="history-row border-b border-gray-50 transition">
                                <td class="px-5 py-3 font-mono text-sm text-gray-600">
                                    {{ $rate->effective_date->format('Y-m-d') }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="text-xl font-black text-teal-600">
                                        {{ number_format($rate->rate, 2) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    @if($rate->change_percent > 0)
                                        <span class="badge-down">▲ +{{ $rate->change_percent }}%</span>
                                    @elseif($rate->change_percent < 0)
                                        <span class="badge-up">▼ {{ $rate->change_percent }}%</span>
                                    @else
                                        <span class="badge-stable">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-500 font-mono text-sm">
                                    {{ $rate->previous_rate ? number_format($rate->previous_rate, 2) : '—' }}
                                </td>
                                <td class="px-5 py-3 text-gray-500 text-sm max-w-xs truncate">
                                    {{ $rate->notes ?: '—' }}
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-600">
                                    {{ $rate->creator?->name ?? '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    @if($rate->is_active)
                                        <span class="bg-teal-100 text-teal-700 text-xs font-bold px-3 py-1 rounded-full">
                                            🟢 فعّال
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">سابق</span>
                                        @if(!$rate->is_active && auth()->user()->hasRole('مدير عام'))
                                        <form method="POST"
                                              action="{{ route('exchange-rates.destroy', $rate) }}"
                                              class="inline mr-1"
                                              onsubmit="return confirm('حذف هذا السجل؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-400 hover:text-red-600 text-xs ml-1"
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-400 py-12">
                                    <div class="text-5xl mb-3">📊</div>
                                    <div>لا يوجد سجل أسعار بعد</div>
                                    <div class="text-sm mt-1">أضف أول سعر صرف من النموذج على اليمين</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($history->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $history->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ─── معاينة السعر الفوري ────────────────────────────────────────────────────
function updatePreview() {
    const rate    = parseFloat(document.getElementById('newRate').value);
    const preview = document.getElementById('previewAmount');
    if (rate > 0) {
        preview.textContent = (rate * 100).toLocaleString('ar-SD', {minimumFractionDigits: 0}) + ' ج.س';
        document.getElementById('sdgPreview').style.borderColor = '#22c55e';
    } else {
        preview.textContent = 'اكتب السعر أعلاه';
        document.getElementById('sdgPreview').style.borderColor = '#86efac';
    }
}

// ─── الرسم البياني ───────────────────────────────────────────────────────────
@if($chartData->count() > 1)
const ctx = document.getElementById('rateChart').getContext('2d');
const chartData = @json($chartData);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData.map(d => d.date),
        datasets: [{
            label: 'سعر الصرف (ج.س/$)',
            data: chartData.map(d => d.rate),
            borderColor: '#0d9488',
            backgroundColor: 'rgba(13,148,136,.08)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#0d9488',
            pointRadius: 4,
            pointHoverRadius: 7,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.parsed.y.toLocaleString('ar')} ج.س/$`
                }
            }
        },
        scales: {
            y: {
                grid: { color: '#f3f4f6' },
                ticks: { callback: v => v.toLocaleString('ar') }
            },
            x: { grid: { display: false } }
        }
    }
});
@endif
</script>
@endpush
