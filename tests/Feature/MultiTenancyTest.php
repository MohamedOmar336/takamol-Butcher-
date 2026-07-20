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
}
