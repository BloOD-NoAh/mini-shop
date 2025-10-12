<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop integer columns and re-add as decimal(10,2)
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['net_price_cents', 'tax_cents', 'selling_price_cents']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('net_price_cents', 10, 2)->nullable()->after('price_cents');
            $table->decimal('tax_cents', 10, 2)->nullable()->after('net_price_cents');
            $table->decimal('selling_price_cents', 10, 2)->nullable()->after('tax_cents');
        });
    }

    public function down(): void
    {
        // Revert back to integers
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['net_price_cents', 'tax_cents', 'selling_price_cents']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('net_price_cents')->nullable()->after('price_cents');
            $table->integer('tax_cents')->nullable()->after('net_price_cents');
            $table->integer('selling_price_cents')->nullable()->after('tax_cents');
        });
    }
};

