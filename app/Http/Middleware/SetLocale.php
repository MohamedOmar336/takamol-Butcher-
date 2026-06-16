<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('locale')) {
            $locale = session()->get('locale');
            if (in_array($locale, ['en', 'ar'])) {
                app()->setLocale($locale);
            }
        } else {
            // Default to Arabic as requested
            app()->setLocale('ar');
        }

        return $next($request);
    }
}
