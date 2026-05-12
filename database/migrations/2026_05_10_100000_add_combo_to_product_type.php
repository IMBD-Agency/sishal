<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change type column to include 'combo'
        DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('product', 'service', 'combo') DEFAULT 'product'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('product', 'service') DEFAULT 'product'");
    }
};
