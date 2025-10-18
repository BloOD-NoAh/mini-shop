<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->cascadeOnDelete();
        });

        // Adjust unique to include variant id
        Schema::table('cart_items', function (Blueprint $table) {
            // Original unique name is usually cart_items_user_id_product_id_unique
            $table->dropUnique('cart_items_user_id_product_id_unique');
            $table->unique(['user_id', 'product_id', 'product_variant_id'], 'cart_items_user_product_variant_unique');
            $table->index(['user_id', 'product_id', 'product_variant_id'], 'cart_items_user_product_variant_index');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('cart_items_user_product_variant_index');
            $table->dropUnique('cart_items_user_product_variant_unique');
            $table->unique(['user_id', 'product_id']);
            $table->dropConstrainedForeignId('product_variant_id');
        });
    }
};
