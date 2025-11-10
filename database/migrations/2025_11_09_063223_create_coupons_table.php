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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Coupon code');
            $table->string('name')->nullable()->comment('Coupon name/description');
            $table->enum('type', ['percentage', 'fixed'])->default('percentage')->comment('Discount type: percentage or fixed amount');
            $table->decimal('value', 10, 2)->default(0)->comment('Discount value (percentage or fixed amount)');
            $table->decimal('min_purchase', 10, 2)->nullable()->comment('Minimum purchase amount required');
            $table->decimal('max_discount', 10, 2)->nullable()->comment('Maximum discount amount (for percentage coupons)');
            $table->integer('usage_limit')->nullable()->comment('Total usage limit (null = unlimited)');
            $table->integer('used_count')->default(0)->comment('Number of times used');
            $table->integer('user_limit')->default(1)->comment('Usage limit per user');
            $table->dateTime('start_date')->nullable()->comment('Coupon start date');
            $table->dateTime('end_date')->nullable()->comment('Coupon expiry date');
            $table->boolean('is_active')->default(true)->comment('Whether coupon is active');
            $table->text('description')->nullable()->comment('Coupon description');
            
            // Scope settings - null means all products/categories
            $table->enum('scope_type', ['all', 'categories', 'products', 'exclude_categories', 'exclude_products'])->default('all')->comment('Scope of coupon application');
            $table->json('applicable_categories')->nullable()->comment('Applicable category IDs (null = all categories)');
            $table->json('applicable_products')->nullable()->comment('Applicable product IDs (null = all products)');
            $table->json('excluded_categories')->nullable()->comment('Excluded category IDs');
            $table->json('excluded_products')->nullable()->comment('Excluded product IDs');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better query performance
            $table->index('code');
            $table->index('is_active');
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['is_active', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
