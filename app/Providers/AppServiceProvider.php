<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PosTransaction;
use App\Models\Product;
use App\Models\StockMovement;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
use App\Observers\PosTransactionObserver;
use App\Observers\ProductObserver;
use App\Observers\StockMovementObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ─── Observers ───────────────────────────────────────────────
        Invoice::observe(InvoiceObserver::class);
        Payment::observe(PaymentObserver::class);
        Product::observe(ProductObserver::class);
        StockMovement::observe(StockMovementObserver::class);
        PosTransaction::observe(PosTransactionObserver::class);

        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }

        Gate::before(function ($user, string $ability) {
            if ($user && method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability);
            }

            return null;
        });

        Blade::if('canPermission', function (string $permission): bool {
            $user = auth()->user();

            return $user !== null && $user->hasPermission($permission);
        });

        View::composer('layouts.app', function ($view): void {
            if ($view->offsetExists('lang')) {
                return;
            }

            $locale = Session::get('locale', config('app.locale', 'ar'));
            if (! in_array($locale, ['ar', 'en'], true)) {
                $locale = 'ar';
            }

            $view->with('lang', $locale);
            $view->with('dir', $locale === 'ar' ? 'rtl' : 'ltr');
        });
    }
}
