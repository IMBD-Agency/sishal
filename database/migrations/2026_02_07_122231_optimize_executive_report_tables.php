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
        // POS Table
        Schema::table('pos', function (Blueprint $table) {
            $indexes = Schema::getIndexes('pos');
            $indexNames = array_column($indexes, 'name');
            
            if (!in_array('pos_sale_date_index', $indexNames)) {
                $table->index('sale_date');
            }
            if (!in_array('pos_branch_id_index', $indexNames)) {
                $table->index('branch_id');
            }
            if (!in_array('pos_status_index', $indexNames)) {
                $table->index('status');
            }
        });

        // POS Items
        Schema::table('pos_items', function (Blueprint $table) {
            $indexes = Schema::getIndexes('pos_items');
            $indexNames = array_column($indexes, 'name');
            
            if (!in_array('pos_items_pos_sale_id_index', $indexNames)) {
                $table->index('pos_sale_id');
            }
        });

        // Orders
        Schema::table('orders', function (Blueprint $table) {
            $indexes = Schema::getIndexes('orders');
            $indexNames = array_column($indexes, 'name');
            
            if (!in_array('orders_status_index', $indexNames)) {
                $table->index('status');
            }
            if (!in_array('orders_created_at_index', $indexNames)) {
                $table->index('created_at');
            }
        });

        // Order Items
        Schema::table('order_items', function (Blueprint $table) {
            $indexes = Schema::getIndexes('order_items');
            $indexNames = array_column($indexes, 'name');
            
            if (!in_array('order_items_order_id_index', $indexNames)) {
                $table->index('order_id');
            }
        });

        // Salary Payments
        Schema::table('salary_payments', function (Blueprint $table) {
            $indexes = Schema::getIndexes('salary_payments');
            $indexNames = array_column($indexes, 'name');
            
            if (!in_array('salary_payments_payment_date_index', $indexNames)) {
                $table->index('payment_date');
            }
        });

        // Supplier Payments
        Schema::table('supplier_payments', function (Blueprint $table) {
            $indexes = Schema::getIndexes('supplier_payments');
            $indexNames = array_column($indexes, 'name');
            
            if (!in_array('supplier_payments_payment_date_index', $indexNames)) {
                $table->index('payment_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback logic is complex due to checks, but standard drop attempts work
        try { Schema::table('pos', fn($t) => $t->dropIndex(['sale_date', 'branch_id', 'status'])); } catch (\Exception $e) {}
        try { Schema::table('pos_items', fn($t) => $t->dropIndex(['pos_sale_id'])); } catch (\Exception $e) {}
        try { Schema::table('orders', fn($t) => $t->dropIndex(['status', 'created_at'])); } catch (\Exception $e) {}
        try { Schema::table('order_items', fn($t) => $t->dropIndex(['order_id'])); } catch (\Exception $e) {}
        try { Schema::table('salary_payments', fn($t) => $t->dropIndex(['payment_date'])); } catch (\Exception $e) {}
        try { Schema::table('supplier_payments', fn($t) => $t->dropIndex(['payment_date'])); } catch (\Exception $e) {}
    }
};
