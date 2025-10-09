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
        Schema::create('product_variation_combinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_id')->constrained('product_variations')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('variation_attributes')->onDelete('cascade');
            $table->foreignId('attribute_value_id')->constrained('variation_attribute_values')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['variation_id', 'attribute_id']);
            $table->index(['variation_id', 'attribute_value_id'], 'pvc_variation_attr_value_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variation_combinations');
    }
};
