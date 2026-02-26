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
        Schema::table('products', function (Blueprint $table) {
            // Adding search index for Style Number and Name to ensure "Global Search" remains fast as DB grows
            if (Schema::hasColumn('products', 'style_number')) {
                $table->index('style_number', 'products_style_number_idx');
            }
            $table->index('name', 'products_name_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_style_number_idx');
            $table->dropIndex('products_name_idx');
        });
    }
};
