<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * التحقق من صلاحية المستخدم قبل الوصول للـ route
     * الاستخدام في routes: middleware('permission:products.create')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'ليس لديك صلاحية للقيام بهذا الإجراء.',
                ], 403);
            }

            abort(403, 'ليس لديك صلاحية للوصول لهذه الصفحة.');
        }

        return $next($request);
    }
}
