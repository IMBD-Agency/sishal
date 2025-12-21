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
        if (!Schema::hasColumn('sale_return_items', 'variation_id')) {
            Schema::table('sale_return_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variation_id')->nullable()->after('product_id');
                $table->foreign('variation_id')->references('id')->on('product_variations')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sale_return_items', 'variation_id')) {
            Schema::table('sale_return_items', function (Blueprint $table) {
                $table->dropForeign(['variation_id']);
                $table->dropColumn('variation_id');
            });
        }
    }
};

