<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('price_cents');
            $table->decimal('net_price', 10, 2)->nullable()->after('price');
            $table->decimal('tax', 10, 2)->nullable()->after('net_price');
        });

        // Backfill from existing integer-cent columns if present
        if (Schema::hasColumn('product_variants', 'price_cents')) {
            DB::table('product_variants')->whereNotNull('price_cents')->update([
                'price' => DB::raw('ROUND(price_cents / 100, 2)')
            ]);
        }
        if (Schema::hasColumn('product_variants', 'net_price_cents')) {
            DB::table('product_variants')->whereNotNull('net_price_cents')->update([
                'net_price' => DB::raw('ROUND(net_price_cents / 100, 2)')
            ]);
        }
        if (Schema::hasColumn('product_variants', 'tax_cents')) {
            DB::table('product_variants')->whereNotNull('tax_cents')->update([
                'tax' => DB::raw('ROUND(tax_cents / 100, 2)')
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['price', 'net_price', 'tax']);
        });
    }
};

