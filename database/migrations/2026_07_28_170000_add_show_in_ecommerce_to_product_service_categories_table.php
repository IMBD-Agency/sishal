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
        Schema::table('product_service_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('product_service_categories', 'show_in_ecommerce')) {
                $table->boolean('show_in_ecommerce')->default(true)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_service_categories', function (Blueprint $table) {
            if (Schema::hasColumn('product_service_categories', 'show_in_ecommerce')) {
                $table->dropColumn('show_in_ecommerce');
            }
        });
    }
};
