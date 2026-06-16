<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique()->index();
            $table->string('name_en');
            $table->string('name_ar');
            $table->decimal('price', 8, 2); // Price in EGP
            $table->enum('pricing_type', ['weight', 'piece']); // Weight (per kg) or Piece (per unit)
            $table->decimal('stock', 8, 3)->default(0.000); // 3 decimals to support grams (e.g. 1.250 kg)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
