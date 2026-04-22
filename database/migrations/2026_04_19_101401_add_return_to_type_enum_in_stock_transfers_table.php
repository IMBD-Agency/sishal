<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            // Laravel doesn't support enum modification natively, use raw statement
        });
        DB::statement("ALTER TABLE stock_transfers MODIFY COLUMN `type` ENUM('request','transfer','return') NOT NULL DEFAULT 'transfer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            // Revert: drop 'return' from enum
        });
        DB::statement("ALTER TABLE stock_transfers MODIFY COLUMN `type` ENUM('request','transfer') NOT NULL DEFAULT 'transfer'");
    }
};
