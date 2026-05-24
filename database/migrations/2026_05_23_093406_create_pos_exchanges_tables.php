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
        Schema::create('pos_exchanges', function (Blueprint $table) {
            $table->id();
            $table->string('exchange_number')->unique();
            $table->unsignedBigInteger('original_pos_id')->nullable()->comment('Legacy link or current reference to POS');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable()->comment('Processed by');
            $table->date('exchange_date');
            
            $table->string('exchange_type', 50)->default('product_exchange')->comment('variation_exchange, product_exchange, price_adjustment');
            
            // Financials
            $table->decimal('total_return_amount', 12, 2)->default(0);
            $table->decimal('total_new_amount', 12, 2)->default(0);
            $table->decimal('delivery_charge', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('extra_payable', 12, 2)->default(0)->comment('Amount customer needs to pay');
            $table->decimal('refund_amount', 12, 2)->default(0)->comment('Amount to refund to customer');
            
            // Payment info (if there's a difference)
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('payment_method')->nullable(); // Cash, Bank, Mobile Banking
            
            $table->string('status', 30)->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Note: Not adding explicit foreign key constraints immediately if existing DB might have messy data
            // but normally we would: $table->foreign('original_pos_id')->references('id')->on('pos');
        });

        Schema::create('pos_exchange_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_exchange_id');
            $table->enum('type', ['returned', 'new']);
            
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variation_id')->nullable();
            
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            
            $table->timestamps();

            $table->foreign('pos_exchange_id')->references('id')->on('pos_exchanges')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_exchange_items');
        Schema::dropIfExists('pos_exchanges');
    }
};
