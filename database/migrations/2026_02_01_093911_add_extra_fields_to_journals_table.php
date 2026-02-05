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
        Schema::table('journals', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->foreignId('expense_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->decimal('voucher_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('reference')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['expense_account_id']);
            $table->dropColumn(['branch_id', 'customer_id', 'supplier_id', 'expense_account_id', 'voucher_amount', 'paid_amount', 'reference']);
        });
    }
};
