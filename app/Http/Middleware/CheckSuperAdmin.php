<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            Auth::logout();
            
            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('super_admin.login')->with('error', 'Unauthorized access / غير مصرح بالدخول');
        }

        return $next($request);
    }
}
