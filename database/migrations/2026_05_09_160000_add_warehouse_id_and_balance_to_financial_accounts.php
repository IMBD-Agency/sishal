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
        Schema::table('financial_accounts', function (Blueprint $table) {
            // Add warehouse_id if not exists
            if (!Schema::hasColumn('financial_accounts', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('branch_id')->constrained('warehouses')->onDelete('set null');
            }
            
            // Add balance column if not exists
            if (!Schema::hasColumn('financial_accounts', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0)->after('mobile_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('financial_accounts', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
            if (Schema::hasColumn('financial_accounts', 'balance')) {
                $table->dropColumn('balance');
            }
        });
    }
};
