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
        Schema::table('salary_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_payments', 'festival_bonus_amount')) {
                $table->decimal('festival_bonus_amount', 12, 2)->default(0)->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn('festival_bonus_amount');
        });
    }
};



