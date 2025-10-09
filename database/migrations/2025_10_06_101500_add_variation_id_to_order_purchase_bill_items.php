<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('order_items', 'variation_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variation_id')->nullable()->after('product_id');
            });
        }
        if (!Schema::hasColumn('purchase_items', 'variation_id')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variation_id')->nullable()->after('product_id');
            });
        }
        if (!Schema::hasColumn('bill_items', 'variation_id')) {
            Schema::table('bill_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variation_id')->nullable()->after('product_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'variation_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('variation_id');
            });
        }
        if (Schema::hasColumn('purchase_items', 'variation_id')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->dropColumn('variation_id');
            });
        }
        if (Schema::hasColumn('bill_items', 'variation_id')) {
            Schema::table('bill_items', function (Blueprint $table) {
                $table->dropColumn('variation_id');
            });
        }
    }
};


