<?php

// المسار: app/Http/Controllers/ExchangeRateController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ExchangeRate;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExchangeRateController extends Controller
{
    // ─── الصفحة الرئيسية ─────────────────────────────────────────────

    public function index(): View
    {
        // السعر الحالي
        $currentRate = ExchangeRate::where('is_active', true)
                                   ->orderBy('effective_date', 'desc')
                                   ->first();

        // السجل التاريخي — آخر 60 سعر
        $history = ExchangeRate::with('creator')
                               ->orderBy('effective_date', 'desc')
                               ->orderBy('created_at', 'desc')
                               ->paginate(20);

        // إحصائيات
        $stats = ExchangeRate::getStats();

        // بيانات الرسم البياني — آخر 30 سعر (للعرض)
        $chartData = ExchangeRate::orderBy('effective_date', 'asc')
                                 ->limit(30)
                                 ->get(['effective_date', 'rate'])
                                 ->map(fn($r) => [
                                     'date' => $r->effective_date->format('d/m'),
                                     'rate' => (float) $r->rate,
                                 ]);

        // عدد المنتجات التي ستتأثر
        $productsCount = Product::whereNotNull('price_usd')
                                ->where('price_usd', '>', 0)
                                ->count();

        return view('exchange-rates.index', compact(
            'currentRate', 'history', 'stats', 'chartData', 'productsCount'
        ));
    }

    // ─── حفظ سعر صرف جديد ────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'rate'           => 'required|numeric|min:1|max:999999',
            'effective_date' => 'required|date',
            'notes'          => 'nullable|string|max:500',
        ], [
            'rate.required'           => 'سعر الصرف مطلوب',
            'rate.min'                => 'سعر الصرف يجب أن يكون أكبر من 1',
            'effective_date.required' => 'تاريخ السريان مطلوب',
        ]);

        $previousRate = ExchangeRate::getCurrent();
        $newRate      = (float) $request->rate;

        // تحذير لو نفس السعر
        if (abs($newRate - $previousRate) < 0.01) {
            return back()->with('warning', 'السعر المدخل هو نفس السعر الحالي، لم يتم تغيير أي شيء.');
        }

        // تفعيل السعر الجديد وتحديث جميع المنتجات
        $exchangeRate = ExchangeRate::activateNew(
            newRate:  $newRate,
            date:     $request->effective_date,
            notes:    $request->notes ?? '',
            userId:   auth()->id(),
        );

        // تسجيل النشاط
        $direction = $newRate > $previousRate ? 'ارتفع' : 'انخفض';
        ActivityLog::record(
            'exchange_rate_updated',
            "تحديث سعر الصرف: {$previousRate} ← {$newRate} ج.س/$ ({$direction} {$exchangeRate->change_percent}%)",
            $exchangeRate
        );

        $productsUpdated = Product::whereNotNull('price_usd')->where('price_usd', '>', 0)->count();

        return redirect()->route('exchange-rates.index')
            ->with('success', "✅ تم تحديث سعر الصرف إلى {$newRate} ج.س/$ وتحديث أسعار {$productsUpdated} منتج تلقائياً.");
    }

    // ─── حذف سجل سعر صرف ────────────────────────────────────────────

    public function destroy(ExchangeRate $exchangeRate): RedirectResponse
    {
        // لا يمكن حذف السعر الفعّال
        if ($exchangeRate->is_active) {
            return back()->with('error', 'لا يمكن حذف سعر الصرف الفعّال حالياً.');
        }

        $exchangeRate->delete();

        return back()->with('success', 'تم حذف السجل بنجاح.');
    }

    // ─── API — السعر الحالي (AJAX) ────────────────────────────────

    public function current()
    {
        return response()->json([
            'rate'         => ExchangeRate::getCurrent(),
            'formatted'    => number_format(ExchangeRate::getCurrent(), 2) . ' ج.س/$',
            'stats'        => ExchangeRate::getStats(),
        ]);
    }
}
