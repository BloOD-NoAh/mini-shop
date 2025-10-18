<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->nullable()->unique();
            $table->json('attributes')->nullable();
            $table->integer('price_cents')->nullable();
            $table->integer('stock')->default(0);
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};

