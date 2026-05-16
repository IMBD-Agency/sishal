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
        Schema::table('product_variation_stocks', function (Blueprint $table) {
            $table->decimal('opening_stock', 15, 2)->default(0)->after('quantity');
        });

        Schema::table('branch_product_stocks', function (Blueprint $table) {
            $table->decimal('opening_stock', 15, 2)->default(0)->after('quantity');
        });

        Schema::table('warehouse_product_stocks', function (Blueprint $table) {
            $table->decimal('opening_stock', 15, 2)->default(0)->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variation_stocks', function (Blueprint $table) {
            $table->dropColumn('opening_stock');
        });

        Schema::table('branch_product_stocks', function (Blueprint $table) {
            $table->dropColumn('opening_stock');
        });

        Schema::table('warehouse_product_stocks', function (Blueprint $table) {
            $table->dropColumn('opening_stock');
        });
    }
};
