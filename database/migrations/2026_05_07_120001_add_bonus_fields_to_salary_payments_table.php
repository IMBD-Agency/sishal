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
            if (!Schema::hasColumn('salary_payments', 'bonus_amount')) {
                $table->decimal('bonus_amount', 12, 2)->default(0)->after('paid_amount');
            }
            if (!Schema::hasColumn('salary_payments', 'is_bonus_editable')) {
                $table->boolean('is_bonus_editable')->default(true)->after('bonus_amount');
            }
            if (!Schema::hasColumn('salary_payments', 'sales_target_id')) {
                $table->unsignedBigInteger('sales_target_id')->nullable()->after('is_bonus_editable');
                $table->index('sales_target_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn(['bonus_amount', 'is_bonus_editable', 'sales_target_id']);
        });
    }
};
