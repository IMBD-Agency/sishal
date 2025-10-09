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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('name'); // e.g., "Red - Large", "Blue - Medium"
            $table->decimal('price', 12, 2)->nullable(); // Override base product price
            $table->decimal('cost', 12, 2)->nullable(); // Override base product cost
            $table->decimal('discount', 12, 2)->nullable(); // Override base product discount
            $table->string('image')->nullable(); // Variation specific image
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
