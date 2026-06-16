<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Super admins have all permissions
        if (auth()->user()->is_admin) {
            return $next($request);
        }

        // Check user permission
        if (!auth()->user()->hasPermission($permission)) {
            abort(403, app()->getLocale() === 'ar' ? 'عذراً، ليس لديك الصلاحية الكافية للوصول إلى هذه الصفحة.' : 'Sorry, you do not have permission to access this page.');
        }

        return $next($request);
    }
}
