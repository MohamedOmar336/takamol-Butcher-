<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SuperAdminController extends Controller
{
    /**
     * Show central landing page.
     */
    public function showLanding()
    {
        return view('landing');
    }

    /**
     * Redirect central landing page form to tenant subdomain.
     */
    public function redirectStore(Request $request)
    {
        $request->validate([
            'slug' => 'required|alpha_dash',
        ], [
            'slug.required' => 'Please enter a store slug / الرجاء إدخال اسم المتجر',
            'slug.alpha_dash' => 'Store slug must contain only letters, numbers, and dashes / يجب أن يحتوي الاسم على أحرف وأرقام وشرطات فقط',
        ]);

        $slug = strtolower($request->slug);
        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            return back()->withErrors(['slug' => 'Store not found / المتجر غير موجود'])->withInput();
        }

        $host = $request->getHost();
        $scheme = $request->getScheme();
        $port = $request->getPort();

        // Detect if we are on localhost or custom domain
        $centralDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';

        if ($host === 'localhost' || $host === '127.0.0.1') {
            $redirectUrl = "{$scheme}://{$slug}.localhost" . ($port ? ":{$port}" : "") . "/login";
        } else {
            $redirectUrl = "{$scheme}://{$slug}.{$centralDomain}" . ($port ? ":{$port}" : "") . "/login";
        }

        return redirect($redirectUrl);
    }

    /**
     * Show Super Admin login page.
     */
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('super_admin.dashboard');
        }
        return view('super_admin.login');
    }

    /**
     * Login Super Admin.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->is_admin) {
                $request->session()->regenerate();
                return redirect()->route('super_admin.dashboard');
            }

            Auth::logout();
            return back()->withErrors([
                'email' => 'Unauthorized / غير مصرح لك بالدخول كمدير عام',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials / بيانات الدخول غير صحيحة',
        ])->onlyInput('email');
    }

    /**
     * Logout Super Admin.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super_admin.login');
    }

    /**
     * Display Super Admin Dashboard.
     */
    public function index()
    {
        $tenants = Tenant::orderBy('created_at', 'desc')->get();
        return view('super_admin.dashboard', compact('tenants'));
    }

    /**
     * Store and initialize a new Tenant.
     */
    public function storeTenant(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|alpha_dash|unique:central.tenants,slug', // validate unique slug in central
            'store_type' => 'required|in:butcher,supermarket,clothing,shoes,general,car_service',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255',
            'report_email' => 'nullable|email|max:255',
            'password' => 'required|string|min:6',
        ], [
            'slug.unique' => 'This store slug is already taken / اسم المتجر هذا محجوز بالفعل',
            'slug.alpha_dash' => 'Store slug must contain only letters, numbers, and dashes / يجب أن يحتوي اسم المتجر على أحرف وأرقام وشرطات فقط',
        ]);

        $slug = strtolower($request->slug);
        $dbName = "tenant_{$slug}.sqlite";
        $dbPath = database_path("tenants/{$dbName}");

        // 1. Create SQLite database file
        if (!File::isDirectory(database_path('tenants'))) {
            File::makeDirectory(database_path('tenants'), 0755, true);
        }
        File::put($dbPath, '');

        // 2. Register Tenant in central database
        $tenant = Tenant::create([
            'name' => $request->name,
            'slug' => $slug,
            'db_name' => $dbName,
            'store_type' => $request->store_type,
            'owner_email' => $request->owner_email,
            'status' => 'active',
            'settings' => [
                'currency' => 'EGP',
                'language' => app()->getLocale(),
                'report_email' => $request->report_email,
            ],
        ]);

        // 3. Dynamically switch connection to run migrations and seed tenant admin user
        $originalDb = config('database.connections.sqlite.database');
        config(['database.connections.sqlite.database' => $dbPath]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        try {
            // Run standard tenant migrations
            Artisan::call('migrate', [
                '--database' => 'sqlite',
                '--path' => 'database/migrations',
                '--force' => true,
            ]);

            // Seed tenant permissions
            $permissions = [
                ['slug' => 'access_pos', 'name_en' => 'Access POS Terminal', 'name_ar' => 'دخول نقطة البيع'],
                ['slug' => 'manage_inventory', 'name_en' => 'Manage Inventory & Products', 'name_ar' => 'إدارة المخزون والمنتجات'],
                ['slug' => 'view_reports', 'name_en' => 'View Sales Reports', 'name_ar' => 'عرض التقارير والمبيعات'],
                ['slug' => 'manage_users', 'name_en' => 'Manage Sub-Users & Roles', 'name_ar' => 'إدارة المستخدمين والصلاحيات']
            ];

            $permIds = [];
            foreach ($permissions as $p) {
                $perm = \App\Models\Permission::create($p);
                $permIds[$p['slug']] = $perm->id;
            }

            // Create Tenant Owner User (acts as Tenant Admin)
            $tenantAdmin = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'password' => bcrypt($request->password),
                'is_admin' => true,
            ]);

            // Attach all permissions to the Tenant Owner
            $tenantAdmin->permissions()->attach(array_values($permIds));

        } catch (\Exception $e) {
            // Rollback tenant creation in central database
            config(['database.connections.sqlite.database' => $originalDb]);
            DB::purge('sqlite');
            DB::reconnect('sqlite');

            $tenant->delete();
            File::delete($dbPath);

            return back()->withErrors(['error' => 'Failed to initialize store database: ' . $e->getMessage()]);
        }

        // Restore original central connection configuration
        config(['database.connections.sqlite.database' => $originalDb]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        return redirect()->route('super_admin.dashboard')->with('success', 'Store created successfully! / تم إنشاء المتجر بنجاح!');
    }

    /**
     * Toggle Tenant active/inactive status.
     */
    public function toggleTenantStatus(Tenant $tenant)
    {
        $tenant->status = $tenant->status === 'active' ? 'suspended' : 'active';
        $tenant->save();

        $message = $tenant->status === 'active' ? 'Store activated! / تم تفعيل المتجر!' : 'Store suspended! / تم إيقاف المتجر!';
        return redirect()->route('super_admin.dashboard')->with('success', $message);
    }

    /**
     * Update an existing Tenant's details and custom logo.
     */
    public function updateTenant(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'store_type' => 'required|in:butcher,supermarket,clothing,shoes,general,car_service',
            'owner_email' => 'required|email|max:255',
            'report_email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Save report_email to settings
        $settings = $tenant->settings ?? [];
        $settings['report_email'] = $request->report_email;
        $tenant->settings = $settings;

        // 1. Update tenant owner email inside tenant DB if changed
        if ($tenant->owner_email !== $request->owner_email) {
            $originalDb = config('database.connections.sqlite.database');
            $dbPath = database_path("tenants/{$tenant->db_name}");
            
            if (file_exists($dbPath)) {
                config(['database.connections.sqlite.database' => $dbPath]);
                DB::purge('sqlite');
                DB::reconnect('sqlite');
                
                // Find and update tenant owner admin email
                $owner = User::where('email', $tenant->owner_email)->first();
                if ($owner) {
                    $owner->update(['email' => $request->owner_email]);
                }
                
                config(['database.connections.sqlite.database' => $originalDb]);
                DB::purge('sqlite');
                DB::reconnect('sqlite');
            }
            
            $tenant->owner_email = $request->owner_email;
        }

        // 2. Handle logo file upload
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $filename = 'logo_' . $tenant->slug . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
            
            // Ensure logo directory exists
            $logoDir = public_path('uploads/logos');
            if (!File::isDirectory($logoDir)) {
                File::makeDirectory($logoDir, 0755, true);
            }
            
            // Delete old logo file if it exists
            $settings = $tenant->settings ?? [];
            if (isset($settings['logo'])) {
                $oldPath = public_path($settings['logo']);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            
            // Move new logo file to public path
            $logoFile->move($logoDir, $filename);
            
            // Save relative logo path to settings
            $settings['logo'] = 'uploads/logos/' . $filename;
            $tenant->settings = $settings;
        }

        $tenant->name = $request->name;
        $tenant->store_type = $request->store_type;
        $tenant->save();

        return redirect()->route('super_admin.dashboard')->with('success', 'Store details updated successfully! / تم تحديث بيانات المتجر بنجاح!');
    }
}
