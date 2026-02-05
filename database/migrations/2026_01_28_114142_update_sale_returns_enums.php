<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we can use DB::statement to modify enum
        DB::statement("ALTER TABLE sale_returns MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'processed', 'completed') DEFAULT 'pending'");
        DB::statement("ALTER TABLE sale_returns MODIFY COLUMN refund_type ENUM('none', 'cash', 'bank', 'credit', 'exchange') DEFAULT 'none'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE sale_returns MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'processed') DEFAULT 'pending'");
        DB::statement("ALTER TABLE sale_returns MODIFY COLUMN refund_type ENUM('none', 'cash', 'bank', 'credit') DEFAULT 'none'");
    }
};
