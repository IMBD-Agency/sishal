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
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('User ID (nullable for guest users)');
            $table->string('session_id')->nullable()->comment('For guest users');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('discount_amount', 10, 2)->comment('Discount amount applied');
            $table->decimal('order_total', 10, 2)->comment('Order total before discount');
            $table->timestamps();
            
            // Indexes
            $table->index('coupon_id');
            $table->index('user_id');
            $table->index('order_id');
            $table->index(['coupon_id', 'user_id']);
        });
        
        // Add foreign key constraint for coupon_id only
        // Other relationships (user_id, order_id) are handled in models for flexibility
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
