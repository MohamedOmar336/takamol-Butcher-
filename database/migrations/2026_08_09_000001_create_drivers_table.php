<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            Schema::table('drivers', function (Blueprint $table) {
                if (!Schema::hasColumn('drivers', 'phone')) {
                    $table->string('phone')->nullable()->after('name');
                }
                if (!Schema::hasColumn('drivers', 'vehicle_type')) {
                    $table->string('vehicle_type')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('drivers', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('vehicle_type');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
