<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $centralDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';

        // Check if the request is on the main domain or direct localhost root
        if ($host === $centralDomain || $host === 'localhost' || $host === '127.0.0.1') {
            // Keep default connection (central database)
            return $next($request);
        }

        // Extract subdomain (e.g. takamul from takamul.localhost or takamul.dukkan-pos.com)
        $subdomain = null;
        if (str_ends_with($host, '.' . $centralDomain)) {
            $subdomain = str_replace('.' . $centralDomain, '', $host);
        } elseif (str_ends_with($host, '.localhost')) {
            $subdomain = str_replace('.localhost', '', $host);
        }

        // Ignore standard subdomains like 'www' or 'admin' (which we use for Super Admin)
        if ($subdomain && !in_array($subdomain, ['www', 'admin'])) {
            $tenant = Tenant::where('slug', $subdomain)
                ->orWhere('domain', $host)
                ->first();

            if (!$tenant) {
                abort(404, 'Store not found / المتجر غير موجود');
            }

            if ($tenant->status !== 'active') {
                abort(403, 'This store is suspended or inactive / هذا المتجر معطل حالياً');
            }

            $dbPath = database_path('tenants/' . $tenant->db_name);

            if (!file_exists($dbPath)) {
                abort(500, 'Store database file is missing. Please contact support.');
            }

            // Switch the default sqlite connection to the tenant database
            config(['database.connections.sqlite.database' => $dbPath]);
            DB::purge('sqlite');
            DB::reconnect('sqlite');

            // Share the tenant info globally in views
            view()->share('activeTenant', $tenant);
        }

        return $next($request);
    }
}
