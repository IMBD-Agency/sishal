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
        // MySQL doesn't allow modifying ENUM directly, so we need to use raw SQL
        DB::statement("ALTER TABLE journals MODIFY COLUMN type ENUM('Journal', 'Payment', 'Receipt', 'Contra', 'Adjustment', 'Transfer') NOT NULL DEFAULT 'Journal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE journals MODIFY COLUMN type ENUM('Journal', 'Payment', 'Receipt', 'Contra', 'Adjustment') NOT NULL DEFAULT 'Journal'");
    }
};
