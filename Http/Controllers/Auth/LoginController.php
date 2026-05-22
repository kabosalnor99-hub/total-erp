<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * عرض صفحة تسجيل الدخول
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * معالجة تسجيل الدخول
     * يقبل: البريد الإلكتروني أو رقم الهاتف
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required'    => 'البريد الإلكتروني أو رقم الهاتف مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        $login = $request->input('login');

        // تحديد طريقة تسجيل الدخول (بريد أو هاتف)
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $field     => $login,
            'password' => $request->input('password'),
        ];

        // التحقق من حالة الحساب أولاً
        $user = User::where($field, $login)->first();

        if ($user && $user->status === 'inactive') {
            return back()->withErrors([
                'login' => 'هذا الحساب معطّل. تواصل مع المدير.',
            ])->withInput();
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // تحديث آخر وقت دخول
            Auth::user()->update(['last_login_at' => now()]);

            // تسجيل في سجل النشاط
            ActivityLog::record('login', 'تسجيل دخول ناجح');

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'بيانات الدخول غير صحيحة.',
        ])->withInput();
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request): RedirectResponse
    {
        ActivityLog::record('logout', 'تسجيل خروج');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
