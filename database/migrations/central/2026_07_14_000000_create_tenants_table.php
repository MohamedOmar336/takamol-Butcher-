<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // e.g. takamul, trendy-wear
            $table->string('domain')->unique()->nullable(); // for custom subdomains/domains
            $table->string('db_name'); // e.g. takamul.sqlite
            $table->string('store_type'); // butcher, supermarket, clothing, shoes, general
            $table->string('owner_email');
            $table->string('status')->default('active'); // active, suspended
            $table->json('settings')->nullable(); // custom configurations (currency, logo, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
