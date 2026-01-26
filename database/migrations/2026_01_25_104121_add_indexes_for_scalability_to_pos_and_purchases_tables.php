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
        // POS Table Indexes
        Schema::table('pos', function (Blueprint $table) {
            $table->index('branch_id');
            $table->index('customer_id');
            $table->index('sale_date');
            $table->index('status');
        });

        // POS Items Table Indexes
        Schema::table('pos_items', function (Blueprint $table) {
            $table->index('pos_sale_id');
            $table->index('product_id');
            $table->index('variation_id');
        });

        // Purchases Table Indexes
        Schema::table('purchases', function (Blueprint $table) {
            $table->index('supplier_id');
            $table->index(['ship_location_type', 'location_id'], 'purchase_location_index');
            $table->index('purchase_date');
            $table->index('status');
        });

        // Purchase Items Table Indexes
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->index('purchase_id');
            $table->index('product_id');
            $table->index('variation_id');
        });

        // Purchase Returns Tables
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->index('purchase_id');
            $table->index('supplier_id');
            $table->index('return_date');
            
            // For faster multi-branch reporting on returns
            $table->enum('location_type', ['branch', 'warehouse'])->nullable()->after('supplier_id');
            $table->unsignedBigInteger('location_id')->nullable()->after('location_type');
            $table->index(['location_type', 'location_id'], 'return_location_index');
        });
    }

    public function down(): void
    {
        Schema::table('pos', function (Blueprint $table) {
            $table->dropIndex(['branch_id']); $table->dropIndex(['customer_id']); $table->dropIndex(['sale_date']); $table->dropIndex(['status']);
        });
        Schema::table('pos_items', function (Blueprint $table) {
            $table->dropIndex(['pos_sale_id']); $table->dropIndex(['product_id']); $table->dropIndex(['variation_id']);
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['supplier_id']); $table->dropIndex('purchase_location_index'); $table->dropIndex(['purchase_date']); $table->dropIndex(['status']);
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropIndex(['purchase_id']); $table->dropIndex(['product_id']); $table->dropIndex(['variation_id']);
        });
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropIndex(['purchase_id']); $table->dropIndex(['supplier_id']); $table->dropIndex(['return_date']);
            $table->dropIndex('return_location_index');
            $table->dropColumn(['location_type', 'location_id']);
        });
    }
};
