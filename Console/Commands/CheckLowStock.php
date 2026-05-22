<?php

// المسار: app/Console/Commands/CheckLowStock.php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature   = 'erp:check-low-stock';
    protected $description = 'Check for products below reorder point and notify inventory managers';

    public function handle(): int
    {
        $this->info('Checking low stock levels...');

        // Get all products and calculate current stock
        $products = Product::with('category')
            ->withSum(['stockMovements as stock_in' => fn($q) => $q->where('type', 'in')], 'quantity')
            ->withSum(['stockMovements as stock_out' => fn($q) => $q->where('type', 'out')], 'quantity')
            ->get()
            ->filter(function ($product) {
                $currentStock = ($product->stock_in ?? 0) - ($product->stock_out ?? 0);
                return $currentStock <= $product->reorder_point;
            });

        if ($products->isEmpty()) {
            $this->info('No low stock products found.');
            return self::SUCCESS;
        }

        $count = $products->count();
        $this->warn("Found {$count} low stock product(s).");

        // Get users to notify: admin + inventory managers
        $managers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'inventory_manager']))
            ->get();

        foreach ($managers as $manager) {
            // One consolidated notification listing all low-stock products
            $productList = $products->map(fn($p) => sprintf(
                '%s (%s): %d %s',
                $p->name_ar,
                $p->sku,
                max(0, ($p->stock_in ?? 0) - ($p->stock_out ?? 0)),
                __('units')
            ))->implode(' | ');

            Notification::notify(
                userId: $manager->id,
                type: 'low_stock',
                titleAr: "تنبيه: {$count} منتج وصل للحد الأدنى",
                titleEn: "Alert: {$count} Product(s) Below Reorder Point",
                bodyAr: $productList,
                bodyEn: $productList,
                url: '/inventory/products?filter=low_stock',
                data: ['count' => $count, 'product_ids' => $products->pluck('id')->toArray()],
                icon: 'exclamation-triangle',
                color: 'red'
            );
        }

        $this->info("Notifications sent to {$managers->count()} user(s).");

        return self::SUCCESS;
    }
}
