<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * ضبط لغة التطبيق (عربي RTL / إنجليزي LTR)
     */
    public function handle(Request $request, Closure $next): Response
    {
        // قراءة اللغة من الجلسة أو الافتراضي (عربي)
        $locale = Session::get('locale', config('app.locale', 'ar'));

        // قائمة اللغات المدعومة
        $supportedLocales = ['ar', 'en'];

        if (!in_array($locale, $supportedLocales)) {
            $locale = 'ar';
        }

        App::setLocale($locale);

        // تمرير اتجاه النص للـ view
        view()->share('dir', $locale === 'ar' ? 'rtl' : 'ltr');
        view()->share('lang', $locale);

        return $next($request);
    }
}
