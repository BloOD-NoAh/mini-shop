<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('net_price_cents')->nullable()->after('price_cents');
            $table->integer('tax_cents')->nullable()->after('net_price_cents');
            $table->integer('selling_price_cents')->nullable()->after('tax_cents');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['net_price_cents', 'tax_cents', 'selling_price_cents']);
        });
    }
};

