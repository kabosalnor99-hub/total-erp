<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\PosTransaction;
use Carbon\Carbon;

class AiContextService
{
    public function getContext(string $module): array
    {
        return match($module) {
            'pos'       => $this->posContext(),
            'sales'     => $this->salesContext(),
            'inventory' => $this->inventoryContext(),
            'customers' => $this->customersContext(),
            default     => $this->generalContext(),
        };
    }

    public function getAutoPrompt(string $module): string
    {
        return match($module) {
            'pos'       => 'حلل أداء المبيعات اليوم وأعطِ توصية واحدة للكاشير.',
            'sales'     => 'قارن مبيعات هذا الشهر بالشهر الماضي وحدد أبرز التغييرات.',
            'inventory' => 'حدد المنتجات التي تحتاج إعادة طلب عاجل وأسبابها.',
            'customers' => 'من هم أفضل العملاء هذا الشهر وما نمط شرائهم؟',
            default     => 'قدم ملخصاً سريعاً لأداء المتجر اليوم.',
        };
    }

    // ── بيانات POS ──────────────────────────────
    private function posContext(): array
    {
        $today = Carbon::today();

        return [
            'today_sales' => PosTransaction::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->selectRaw('COUNT(*) as count, SUM(total) as total')
                ->first()?->toArray(),

            'top_products_today' => PosTransaction::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->with('items.product:id,name_ar')
                ->get()
                ->flatMap(fn($t) => $t->items)
                ->groupBy('product_id')
                ->map(fn($items) => [
                    'name'     => $items->first()->product?->name_ar,
                    'quantity' => $items->sum('quantity'),
                    'total'    => $items->sum('total'),
                ])
                ->sortByDesc('total')
                ->take(5)
                ->values()
                ->toArray(),

            'low_stock' => Product::where('quantity', '<=', 10)
                ->where('quantity', '>', 0)
                ->select('name_ar', 'quantity')
                ->orderBy('quantity')
                ->take(5)
                ->get()
                ->toArray(),
        ];
    }

    // ── بيانات المبيعات ──────────────────────────
    private function salesContext(): array
    {
        $thisMonthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd   = Carbon::now()->subMonth()->endOfMonth();

        return [
            'this_month' => Invoice::where('created_at', '>=', $thisMonthStart)
                ->where('status', '!=', 'cancelled')
                ->selectRaw('COUNT(*) as count, SUM(total) as total')
                ->first()?->toArray(),

            'last_month' => Invoice::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->where('status', '!=', 'cancelled')
                ->selectRaw('COUNT(*) as count, SUM(total) as total')
                ->first()?->toArray(),

            'top_products' => Product::withSum(
                    ['invoiceItems as sold' => fn($q) => $q->whereHas(
                        'invoice', fn($q) => $q->where('status', '!=', 'cancelled')
                    )],
                    'quantity'
                )
                ->orderByDesc('sold')
                ->take(5)
                ->get(['name_ar', 'sold'])
                ->toArray(),
        ];
    }

    // ── بيانات المخزون ────────────────────────────
    private function inventoryContext(): array
    {
        return [
            'out_of_stock' => Product::where('quantity', 0)->count(),

            'low_stock' => Product::where('quantity', '>', 0)
                ->where('quantity', '<=', 10)
                ->select('name_ar', 'quantity', 'purchase_price_usd')
                ->orderBy('quantity')
                ->take(10)
                ->get()
                ->toArray(),

            'total_value_usd' => Product::selectRaw('SUM(quantity * purchase_price_usd) as value')
                ->value('value'),
        ];
    }

    // ── بيانات العملاء ────────────────────────────
    private function customersContext(): array
    {
        return [
            'top_customers' => Customer::withSum(
                    ['invoices as total_spent' => fn($q) => $q->where('status', '!=', 'cancelled')],
                    'total'
                )
                ->orderByDesc('total_spent')
                ->take(5)
                ->get(['name', 'phone', 'total_spent'])
                ->toArray(),

            'new_this_month' => Customer::where('created_at', '>=', Carbon::now()->startOfMonth())
                ->count(),

            'total_debt' => Customer::where('balance', '>', 0)->sum('balance'),
        ];
    }

    // ── السياق العام ──────────────────────────────
    private function generalContext(): array
    {
        return array_merge(
            $this->posContext(),
            ['store_name' => 'توتال السودان لمعدات الورش']
        );
    }
}
