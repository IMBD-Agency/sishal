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
        Schema::create('bulk_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Discount name/description');
            $table->decimal('percentage', 5, 2)->comment('Discount percentage (0-100)');
            $table->enum('scope_type', ['all', 'products'])->default('all')->comment('Scope: all products or specific products');
            $table->json('applicable_products')->nullable()->comment('Applicable product IDs (null = all products)');
            $table->dateTime('start_date')->nullable()->comment('Discount start date (null = immediate)');
            $table->dateTime('end_date')->nullable()->comment('Discount end date (null = no expiry)');
            $table->boolean('is_active')->default(true)->comment('Whether discount is active');
            $table->text('description')->nullable()->comment('Discount description');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better query performance
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
        Schema::dropIfExists('bulk_discounts');
    }
};
