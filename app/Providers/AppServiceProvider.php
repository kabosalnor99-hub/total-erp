<?php

namespace App\Providers;

use App\Services\CacheService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // تسجيل CacheService كـ singleton
        $this->app->singleton(CacheService::class);
    }

    public function boot(): void
    {
        // ─── Gate: استخدام hasPermission المحسّن (مع cache) ──────────
        Gate::before(function ($user, string $ability) {
            if ($user && method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability);
            }
            return null;
        });

        // ─── Blade Directive: @canPermission ─────────────────────────
        Blade::if('canPermission', function (string $permission): bool {
            $user = auth()->user();
            return $user !== null && $user->hasPermission($permission);
        });

        // ─── View Composer: layouts.app ──────────────────────────────
        View::composer('layouts.app', function ($view): void {
            // اللغة والاتجاه
            if (! $view->offsetExists('lang')) {
                $locale = Session::get('locale', config('app.locale', 'ar'));
                if (! in_array($locale, ['ar', 'en'], true)) {
                    $locale = 'ar';
                }
                $view->with('lang', $locale);
                $view->with('dir', $locale === 'ar' ? 'rtl' : 'ltr');
            }

            // عدد الإشعارات غير المقروءة (مع cache)
            if (auth()->check()) {
                $userId      = auth()->id();
                $unreadCount = Cache::remember(
                    CacheService::unreadCountKey($userId),
                    CacheService::TTL_NOTIFICATIONS,
                    fn() => \App\Models\Notification::forUser($userId)->unread()->count()
                );
                $view->with('unreadCount', $unreadCount);
            }
        });
    }
}
