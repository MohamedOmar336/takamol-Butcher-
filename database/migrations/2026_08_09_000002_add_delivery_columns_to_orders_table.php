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
        Schema::table('orders', function (Blueprint $table) {
            if (!$this->hasColumn('orders', 'driver_id')) {
                $table->foreignId('driver_id')->nullable()->after('customer_id')->constrained('drivers')->onDelete('set null');
            }
            if (!$this->hasColumn('orders', 'delivery_fee')) {
                $table->decimal('delivery_fee', 8, 2)->default(0.00)->after('discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if ($this->hasColumn('orders', 'driver_id')) {
                $table->dropForeign(['driver_id']);
                $table->dropColumn('driver_id');
            }
            if ($this->hasColumn('orders', 'delivery_fee')) {
                $table->dropColumn('delivery_fee');
            }
        });
    }
};
