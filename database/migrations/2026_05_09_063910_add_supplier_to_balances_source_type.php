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
        Schema::table('balances', function (Blueprint $table) {
            // Change the ENUM to include 'supplier'
            $table->enum('source_type', ['user', 'employee', 'customer', 'supplier'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balances', function (Blueprint $table) {
            // Revert back to original ENUM (only if no supplier records exist)
            $table->enum('source_type', ['user', 'employee', 'customer'])->change();
        });
    }
};
