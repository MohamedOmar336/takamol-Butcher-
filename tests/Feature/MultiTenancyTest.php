<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MultiTenancyTest extends TestCase
{
    /**
     * Test that the central landing page loads successfully.
     */
    public function test_central_landing_page_accessible()
    {
        $response = $this->get('http://localhost/');
        $response->assertStatus(200);
        $response->assertSee('DokkanHub');
    }

    /**
     * Test that requesting a tenant subdomain switches connections to the tenant's SQLite file.
     */
    public function test_tenant_db_switching()
    {
        // 1. Create a dummy tenant record in the central DB
        $slug = 'test-store-' . uniqid();
        $dbName = "tenant_{$slug}.sqlite";
        $dbPath = database_path("tenants/{$dbName}");

        if (!File::isDirectory(database_path('tenants'))) {
            File::makeDirectory(database_path('tenants'), 0755, true);
        }
        File::put($dbPath, '');

        $tenant = Tenant::create([
            'name' => 'Test Store',
            'slug' => $slug,
            'db_name' => $dbName,
            'store_type' => 'clothing',
            'owner_email' => 'test-owner@example.com',
            'status' => 'active',
        ]);

        // 2. Setup the tenant database schema by running migrations on the dummy SQLite file
        $originalDb = config('database.connections.sqlite.database');
        config(['database.connections.sqlite.database' => $dbPath]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--database' => 'sqlite',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        // Seed a test user in the tenant DB
        $user = \App\Models\User::create([
            'name' => 'Test Tenant User',
            'email' => 'tenant-user@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        // Restore original central connection
        config(['database.connections.sqlite.database' => $originalDb]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        // 3. Request the tenant subdomain and verify it finds the login screen and the tenant-scoped context
        $response = $this->get("http://{$slug}.localhost/login");
        $response->assertStatus(200);
        
        // 4. Cleanup dummy files and records
        $tenant->delete();
        File::delete($dbPath);
    }

    /**
     * Test that the single-click secure login bypass works correctly.
     */
    public function test_tenant_login_bypass()
    {
        $slug = 'test-bypass-' . uniqid();
        $dbName = "tenant_{$slug}.sqlite";
        $dbPath = database_path("tenants/{$dbName}");

        if (!File::isDirectory(database_path('tenants'))) {
            File::makeDirectory(database_path('tenants'), 0755, true);
        }
        File::put($dbPath, '');

        $tenant = Tenant::create([
            'name' => 'Bypass Store',
            'slug' => $slug,
            'db_name' => $dbName,
            'store_type' => 'supermarket',
            'owner_email' => 'bypass@example.com',
            'status' => 'active',
        ]);

        $originalDb = config('database.connections.sqlite.database');
        config(['database.connections.sqlite.database' => $dbPath]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--database' => 'sqlite',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        \App\Models\User::create([
            'name' => 'Store Owner',
            'email' => 'admin@bypass.com',
            'password' => bcrypt('secret123'),
            'is_admin' => true,
        ]);

        config(['database.connections.sqlite.database' => $originalDb]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $timestamp = time();
        $signature = hash_hmac('sha256', $slug . '|' . $timestamp, config('app.key'));
        
        $response = $this->get("http://{$slug}.localhost/login/bypass?timestamp={$timestamp}&signature={$signature}");
        
        $response->assertRedirect(route('pos.index'));
        $this->assertAuthenticated();

        $tenant->delete();
        File::delete($dbPath);
    }

    /**
     * Test that a tenant can be deleted via Super Admin.
     */
    public function test_tenant_deletion()
    {
        $slug = 'test-delete-' . uniqid();
        $dbName = "tenant_{$slug}.sqlite";
        $dbPath = database_path("tenants/{$dbName}");

        if (!File::isDirectory(database_path('tenants'))) {
            File::makeDirectory(database_path('tenants'), 0755, true);
        }
        File::put($dbPath, '');

        $tenant = Tenant::create([
            'name' => 'Delete Store',
            'slug' => $slug,
            'db_name' => $dbName,
            'store_type' => 'general',
            'owner_email' => 'delete@example.com',
            'status' => 'active',
        ]);

        $this->assertTrue(File::exists($dbPath));

        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--path' => 'database/migrations/central',
            '--force' => true,
        ]);

        $superAdmin = \App\Models\User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin-' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => true
        ]);
        $this->actingAs($superAdmin);

        $centralDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
        $response = $this->delete("http://{$centralDomain}/super-admin/tenants/{$tenant->id}");

        $response->assertRedirect(route('super_admin.dashboard'));

        $this->assertFalse(File::exists($dbPath));

        $this->assertDatabaseMissing('tenants', [
            'id' => $tenant->id
        ], 'central');
    }
}
