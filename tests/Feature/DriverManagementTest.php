<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Driver;
use App\Models\Order;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailySalesReportMail;
use Carbon\Carbon;
use Tests\TestCase;

class DriverManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Migrate central DB
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--path' => 'database/migrations/central',
            '--force' => true,
        ]);
    }

    public function test_driver_crud_and_report_breakdown()
    {
        $slug = 'test-drivers-' . uniqid();
        $dbName = "tenant_{$slug}.sqlite";
        $dbPath = database_path("tenants/{$dbName}");

        if (!File::isDirectory(database_path('tenants'))) {
            File::makeDirectory(database_path('tenants'), 0755, true);
        }
        File::put($dbPath, '');

        // 1. Create central tenant record
        $tenant = Tenant::create([
            'name' => 'Driver Store',
            'slug' => $slug,
            'db_name' => $dbName,
            'store_type' => 'butcher',
            'owner_email' => 'driver-owner@example.com',
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

        // Seed a test admin user in the tenant DB
        $admin = User::create([
            'name' => 'Store Admin',
            'email' => 'admin@store.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        // Restore original central connection
        config(['database.connections.sqlite.database' => $originalDb]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        // Log in as the admin user
        $this->actingAs($admin);

        // 3. Test CRUD via HTTP Requests to the tenant subdomain
        $baseUrl = "http://{$slug}.localhost";

        // Store Driver
        $response = $this->from("{$baseUrl}/admin/drivers")->post("{$baseUrl}/admin/drivers", [
            'name' => 'Driver Ahmed',
            'phone' => '01234567890',
            'is_active' => '1',
        ]);
        $response->assertRedirect("{$baseUrl}/admin/drivers");

        // Point to tenant DB to assert
        config(['database.connections.sqlite.database' => $dbPath]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->assertDatabaseHas('drivers', [
            'name' => 'Driver Ahmed',
            'phone' => '01234567890',
            'is_active' => 1,
        ]);

        $driver = Driver::where('name', 'Driver Ahmed')->first();

        // Restore central DB to perform next HTTP request
        config(['database.connections.sqlite.database' => $originalDb]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        // Update Driver
        $response = $this->from("{$baseUrl}/admin/drivers")->put("{$baseUrl}/admin/drivers/{$driver->id}", [
            'name' => 'Driver Ahmed Updated',
            'phone' => '09876543210',
            'is_active' => '1',
        ]);
        $response->assertRedirect("{$baseUrl}/admin/drivers");

        // Point to tenant DB to assert and seed orders
        config(['database.connections.sqlite.database' => $dbPath]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->assertDatabaseHas('drivers', [
            'name' => 'Driver Ahmed Updated',
            'phone' => '09876543210',
        ]);

        // 4. Daily Report Mail Driver Breakdown Testing
        Mail::fake();

        // Create completed orders with delivery fee and driver name
        Order::create([
            'order_number' => 'DKN-TEST-1',
            'user_id' => $admin->id,
            'driver_id' => $driver->id,
            'payment_method' => 'cash',
            'total_amount' => 150.00,
            'delivery_fee' => 20.00,
            'delivery_address' => 'Cairo, Egypt',
            'driver_name' => 'Driver Ahmed Updated',
            'status' => 'completed',
            'created_at' => Carbon::now('Africa/Cairo'),
        ]);

        Order::create([
            'order_number' => 'DKN-TEST-2',
            'user_id' => $admin->id,
            'driver_id' => $driver->id,
            'payment_method' => 'cash',
            'total_amount' => 180.00,
            'delivery_fee' => 15.00,
            'delivery_address' => 'Maadi, Cairo',
            'driver_name' => 'Driver Ahmed Updated',
            'status' => 'completed',
            'created_at' => Carbon::now('Africa/Cairo'),
        ]);

        // Run Send Daily Sales Report command
        // We bind the tenant instance in container so the console command knows the active tenant context
        app()->instance('activeTenant', $tenant);

        \Illuminate\Support\Facades\Artisan::call('app:send-daily-sales-report');

        // Assert Mail was sent with driver breakdown
        Mail::assertSent(DailySalesReportMail::class, function ($mail) {
            $stats = $mail->stats;
            $this->assertArrayHasKey('delivery_breakdown', $stats);
            $breakdown = $stats['delivery_breakdown'];
            
            $this->assertCount(1, $breakdown);
            $this->assertEquals('Driver Ahmed Updated', $breakdown[0]['driver_name']);
            $this->assertEquals(2, $breakdown[0]['order_count']);
            $this->assertEquals(35.00, $breakdown[0]['total_fees']);
            
            return true;
        });

        // Restore central DB for final delete request
        config(['database.connections.sqlite.database' => $originalDb]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        // Delete Driver
        $response = $this->from("{$baseUrl}/admin/drivers")->delete("{$baseUrl}/admin/drivers/{$driver->id}");
        $response->assertRedirect("{$baseUrl}/admin/drivers");

        // Point to tenant DB to assert deletion
        config(['database.connections.sqlite.database' => $dbPath]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->assertDatabaseMissing('drivers', [
            'id' => $driver->id,
        ]);

        // Clean up
        config(['database.connections.sqlite.database' => $originalDb]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $tenant->delete();
        File::delete($dbPath);
    }
}
