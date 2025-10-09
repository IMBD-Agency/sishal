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
        // Add columns only if they do not already exist
        if (!Schema::hasColumn('products', 'has_variations')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('has_variations')->default(false)->after('status');
            });
        }

        if (!Schema::hasColumn('products', 'manage_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('manage_stock')->default(true)->after('has_variations');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop columns only if they exist
        if (Schema::hasColumn('products', 'manage_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('manage_stock');
            });
        }

        if (Schema::hasColumn('products', 'has_variations')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('has_variations');
            });
        }
    }
};
