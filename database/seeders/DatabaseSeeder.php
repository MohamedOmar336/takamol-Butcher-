<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Permissions
        $permissions = [
            [
                'slug' => 'access_pos',
                'name_en' => 'Access POS Terminal',
                'name_ar' => 'دخول نقطة البيع'
            ],
            [
                'slug' => 'manage_inventory',
                'name_en' => 'Manage Inventory & Products',
                'name_ar' => 'إدارة المخزون والمنتجات'
            ],
            [
                'slug' => 'view_reports',
                'name_en' => 'View Sales Reports',
                'name_ar' => 'عرض التقارير والمبيعات'
            ],
            [
                'slug' => 'manage_users',
                'name_en' => 'Manage Sub-Users & Roles',
                'name_ar' => 'إدارة المستخدمين والصلاحيات'
            ]
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['slug']] = Permission::create($p);
        }

        // 2. Create Super Admin
        $admin = User::create([
            'name' => 'المدير العام',
            'email' => 'admin@takamul.com',
            'password' => bcrypt('admin123'),
            'is_admin' => true,
        ]);

        // 3. Create Sub-Users with specific access
        $cashier = User::create([
            'name' => 'كاشير مبيعات',
            'email' => 'cashier@takamul.com',
            'password' => bcrypt('cashier123'),
            'is_admin' => false,
        ]);
        $cashier->permissions()->attach($permissionModels['access_pos']->id);

        $manager = User::create([
            'name' => 'مدير فرع',
            'email' => 'manager@takamul.com',
            'password' => bcrypt('manager123'),
            'is_admin' => false,
        ]);
        $manager->permissions()->attach([
            $permissionModels['access_pos']->id,
            $permissionModels['manage_inventory']->id,
            $permissionModels['view_reports']->id
        ]);

        // 4. Create Categories
        $beef = Category::create(['name_en' => 'Fresh Beef', 'name_ar' => 'لحم بقري طازج', 'slug' => 'fresh-beef']);
        $mutton = Category::create(['name_en' => 'Fresh Mutton', 'name_ar' => 'لحم ضأن طازج', 'slug' => 'fresh-mutton']);
        $minced = Category::create(['name_en' => 'Minced & Kofta', 'name_ar' => 'مفروم وكفتة', 'slug' => 'minced-kofta']);
        $spices = Category::create(['name_en' => 'Spices & Accessories', 'name_ar' => 'بهارات ومستلزمات', 'slug' => 'spices-accessories']);

        // 5. Create Products
        // Product matching the customer's scale barcode '2011135015034' (PLU: 01113, Weight: 1.503kg, Price per kg: 280)
        Product::create([
            'category_id' => $spices->id,
            'sku' => '01113',
            'name_en' => 'Kofta Spices',
            'name_ar' => 'توابل كفتة',
            'price' => 280.00,
            'pricing_type' => 'weight',
            'stock' => 25.000,
            'is_active' => true
        ]);

        Product::create([
            'category_id' => $beef->id,
            'sku' => '01111',
            'name_en' => 'Beef Ribeye Baladi',
            'name_ar' => 'ريب آي بقري بلدي',
            'price' => 380.00,
            'pricing_type' => 'weight',
            'stock' => 50.000,
            'is_active' => true
        ]);

        Product::create([
            'category_id' => $beef->id,
            'sku' => '01112',
            'name_en' => 'Beef Cubes',
            'name_ar' => 'كندوز مكعبات بلدي',
            'price' => 340.00,
            'pricing_type' => 'weight',
            'stock' => 75.000,
            'is_active' => true
        ]);

        Product::create([
            'category_id' => $mutton->id,
            'sku' => '01114',
            'name_en' => 'Lamb Chops Baladi',
            'name_ar' => 'ريش ضأن بلدي',
            'price' => 420.00,
            'pricing_type' => 'weight',
            'stock' => 30.000,
            'is_active' => true
        ]);

        Product::create([
            'category_id' => $spices->id,
            'sku' => '01115',
            'name_en' => 'Small Cleaver Knife',
            'name_ar' => 'ساطور جزارة صغير',
            'price' => 150.00,
            'pricing_type' => 'piece',
            'stock' => 10.000,
            'is_active' => true
        ]);
    }
}
