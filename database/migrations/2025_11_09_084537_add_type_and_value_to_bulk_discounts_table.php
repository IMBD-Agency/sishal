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
        Schema::table('bulk_discounts', function (Blueprint $table) {
            // Add type field (percentage or fixed)
            $table->enum('type', ['percentage', 'fixed'])->default('percentage')->after('name')->comment('Discount type: percentage or fixed amount');
            
            // Add value field (replaces percentage, can be percentage or fixed amount)
            $table->decimal('value', 10, 2)->nullable()->after('type')->comment('Discount value (percentage or fixed amount)');
            
            // Make percentage nullable (for backward compatibility, will be removed later)
            $table->decimal('percentage', 5, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulk_discounts', function (Blueprint $table) {
            $table->dropColumn(['type', 'value']);
            $table->decimal('percentage', 5, 2)->nullable(false)->change();
        });
    }
};
