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
        if (!Schema::hasTable('drivers')) {
            Schema::create('drivers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('vehicle_type')->nullable(); // e.g. دراجة نارية / موتوسيكل / سيارة
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } else {
            if (!$this->hasColumn('drivers', 'phone')) {
                DB::statement('ALTER TABLE drivers ADD COLUMN phone VARCHAR(255) NULL');
            }
            if (!$this->hasColumn('drivers', 'vehicle_type')) {
                DB::statement('ALTER TABLE drivers ADD COLUMN vehicle_type VARCHAR(255) NULL');
            }
            if (!$this->hasColumn('drivers', 'is_active')) {
                DB::statement('ALTER TABLE drivers ADD COLUMN is_active TINYINT(1) DEFAULT 1');
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
