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
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_number')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->date('requisition_date');
            $table->enum('status', ['pending', 'partially_fulfilled', 'fulfilled', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        Schema::create('requisition_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requisition_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->decimal('quantity', 15, 2);
            $table->decimal('fulfilled_quantity', 15, 2)->default(0);
            $table->enum('status', ['pending', 'fulfilled', 'rejected'])->default('pending');
            $table->timestamps();
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('requisition_item_id')->nullable();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->unsignedBigInteger('requisition_item_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requisition_item_id');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requisition_item_id');
        });

        Schema::dropIfExists('requisition_items');
        Schema::dropIfExists('requisitions');
    }
};
