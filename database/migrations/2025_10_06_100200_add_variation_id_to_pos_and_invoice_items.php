<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pos_items', 'variation_id')) {
            Schema::table('pos_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variation_id')->nullable()->after('product_id');
            });
        }

        if (!Schema::hasColumn('invoice_items', 'variation_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variation_id')->nullable()->after('product_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pos_items', 'variation_id')) {
            Schema::table('pos_items', function (Blueprint $table) {
                $table->dropColumn('variation_id');
            });
        }

        if (Schema::hasColumn('invoice_items', 'variation_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropColumn('variation_id');
            });
        }
    }
};


