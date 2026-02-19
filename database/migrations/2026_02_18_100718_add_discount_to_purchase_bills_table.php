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
        Schema::table('purchase_bills', function (Blueprint $table) {
            $table->decimal('sub_total', 15, 2)->after('bill_date')->default(0);
            $table->decimal('discount_amount', 15, 2)->after('sub_total')->default(0);
            $table->string('discount_type')->after('discount_amount')->default('flat');
            $table->decimal('discount_value', 15, 2)->after('discount_type')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_bills', function (Blueprint $table) {
            $table->dropColumn(['sub_total', 'discount_amount', 'discount_type', 'discount_value']);
        });
    }
};
