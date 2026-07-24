<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class MigrateTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate {--tenant= : Migrate a specific tenant slug}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Run migrations on all tenant databases or a specific tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantSlug = $this->option('tenant');

        if ($tenantSlug) {
            $tenants = Tenant::where('slug', $tenantSlug)->get();
            if ($tenants->isEmpty()) {
                $this->error("Tenant '{$tenantSlug}' not found.");
                return 1;
            }
        } else {
            $tenants = Tenant::all();
            if ($tenants->isEmpty()) {
                $this->info("No tenants found in the central database.");
                return 0;
            }
        }

        $this->info("Starting migrations for " . $tenants->count() . " tenant(s)...");

        // Save original connection path
        $originalDb = config('database.connections.sqlite.database');

        foreach ($tenants as $tenant) {
            $dbPath = database_path('tenants/' . $tenant->db_name);

            if (!file_exists($dbPath)) {
                $this->warn("Database file not found for tenant: {$tenant->name}. Creating it now...");
                // Create empty sqlite file
                touch($dbPath);
            }

            $this->info("========================================");
            $this->info("Migrating Tenant: {$tenant->name} [{$tenant->slug}]");
            $this->info("Database: {$dbPath}");
            $this->info("========================================");

            // Reconfigure database connection dynamically
            config(['database.connections.sqlite.database' => $dbPath]);
            DB::purge('sqlite');
            DB::reconnect('sqlite');

            // Run standard tenant migrations (from root database/migrations)
            Artisan::call('migrate', [
                '--database' => 'sqlite',
                '--path' => 'database/migrations',
                '--force' => true,
            ]);

            // If tenant is car_service, run Car Service workshop migrations
            $carServiceMigrations = 'c:/xampp/htdocs/car-service-system/database/migrations';
            if ($tenant->store_type === 'car_service' && \Illuminate\Support\Facades\File::isDirectory($carServiceMigrations)) {
                Artisan::call('migrate', [
                    '--database' => 'sqlite',
                    '--path' => $carServiceMigrations,
                    '--realpath' => true,
                    '--force' => true,
                ]);
            }

            $this->line(Artisan::output());
        }

        // Restore original central connection configuration
        config(['database.connections.sqlite.database' => $originalDb]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->info("Tenant migrations completed.");
        return 0;
    }
}
