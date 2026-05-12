<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combo_products', function (Blueprint $table) {
            $table->id();
            // Combo parent product (type = 'combo')
            $table->unsignedBigInteger('combo_product_id');
            // Individual product in combo
            $table->unsignedBigInteger('product_id');
            // Optional: variation if product has variations
            $table->unsignedBigInteger('variation_id')->nullable();
            // Quantity of this product in combo
            $table->integer('quantity')->default(1);
            // Optional: custom price for this item in combo (for discount calculation)
            $table->decimal('combo_price', 12, 2)->nullable();
            $table->timestamps();

            // Foreign keys - manually add if needed
            // $table->foreign('combo_product_id')->references('id')->on('products')->onDelete('cascade');
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // $table->foreign('variation_id')->references('id')->on('product_variations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_products');
    }
};
