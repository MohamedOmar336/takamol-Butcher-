<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CentralDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Super Admin User (Requested credentials: mhmedomr336@gmail.com / 12345678)
        User::create([
            'name' => 'Super Admin',
            'email' => 'mhmedomr336@gmail.com',
            'password' => bcrypt('12345678'),
            'is_admin' => true,
        ]);

        // 2. Register the existing butcher shop "Al-Takamul" as the first tenant
        Tenant::create([
            'name' => 'ملحمة التكامل',
            'slug' => 'takamul',
            'db_name' => 'takamul.sqlite',
            'store_type' => 'butcher',
            'owner_email' => 'admin@takamul.com',
            'status' => 'active',
            'settings' => [
                'currency' => 'EGP',
                'language' => 'ar',
            ],
        ]);
    }
}
