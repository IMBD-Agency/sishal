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
        Schema::table('pos', function (Blueprint $table) {
            $table->string('challan_number')->nullable()->after('sale_number');
            $table->string('sale_type')->nullable()->after('sale_date'); // MRP, Wholesale
            $table->string('account_type')->nullable()->after('notes');
            $table->string('account_number')->nullable()->after('account_type');
            $table->text('remarks')->nullable()->after('account_number');
            $table->unsignedBigInteger('courier_id')->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos', function (Blueprint $table) {
            //
        });
    }
};
