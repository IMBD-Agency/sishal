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
        Schema::table('pos', function (Blueprint $table) {
            $table->decimal('tendered_amount', 12, 2)->nullable()->default(0)->after('total_amount');
            $table->decimal('change_amount', 12, 2)->nullable()->default(0)->after('tendered_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos', function (Blueprint $table) {
            $table->dropColumn(['tendered_amount', 'change_amount']);
        });
    }
};
