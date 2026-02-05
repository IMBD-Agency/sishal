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
        // Changing enum to string to support more flexible types like 'manual_receipt'
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_for VARCHAR(50) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting back to enum is risky if data doesn't match, sticking to string or best effort
        // schema::table('payments', function (Blueprint $table) { ... });
        // For safety, we can leave it as string or try to revert if valid data.
        // DB::statement("ALTER TABLE payments MODIFY COLUMN payment_for ENUM('pos','invoice','order','service') NULL");
    }
};
