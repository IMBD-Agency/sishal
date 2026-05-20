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
        Schema::table('sales_targets', function (Blueprint $table) {
            // Make employee_id nullable since targets are now branch-wise
            $table->unsignedBigInteger('employee_id')->nullable()->change();

            // Add new target and achievement fields for branch-wise quantity targets
            $table->decimal('target_quantity', 12, 2)->default(0)->after('branch_id');
            $table->decimal('incentive_amount', 12, 2)->default(0)->after('target_quantity');
            $table->decimal('commission_per_extra_sale', 12, 2)->default(0)->after('incentive_amount');
            
            $table->decimal('achieved_quantity', 12, 2)->default(0)->after('achieved_amount');
            $table->decimal('achieved_incentive', 12, 2)->default(0)->after('achieved_quantity');
            $table->decimal('achieved_extra_commission', 12, 2)->default(0)->after('achieved_incentive');
            $table->decimal('total_achieved_bonus', 12, 2)->default(0)->after('achieved_extra_commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable(false)->change();

            $table->dropColumn([
                'target_quantity',
                'incentive_amount',
                'commission_per_extra_sale',
                'achieved_quantity',
                'achieved_incentive',
                'achieved_extra_commission',
                'total_achieved_bonus'
            ]);
        });
    }
};
