<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function hasColumn($table, $column): bool
    {
        try {
            $columns = DB::select("PRAGMA table_info({$table})");
            foreach ($columns as $col) {
                $colArray = (array) $col;
                if (isset($colArray['name']) && strtolower($colArray['name']) === strtolower($column)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // fallback
        }
        return false;
    }

    public function up(): void
    {
        if (!$this->hasColumn('orders', 'driver_id')) {
            DB::statement('ALTER TABLE orders ADD COLUMN driver_id INTEGER NULL REFERENCES drivers(id) ON DELETE SET NULL');
        }
        if (!$this->hasColumn('orders', 'delivery_fee')) {
            DB::statement('ALTER TABLE orders ADD COLUMN delivery_fee DECIMAL(8, 2) DEFAULT 0.00');
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE orders DROP COLUMN driver_id');
        } catch (\Exception $e) {}
        try {
            DB::statement('ALTER TABLE orders DROP COLUMN delivery_fee');
        } catch (\Exception $e) {}
    }
};
