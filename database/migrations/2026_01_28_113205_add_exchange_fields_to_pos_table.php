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
            $table->decimal('exchange_amount', 12, 2)->default(0)->after('delivery');
            $table->unsignedBigInteger('original_pos_id')->nullable()->after('exchange_amount');
            
            // Adding index for faster search if needed
            $table->index('original_pos_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos', function (Blueprint $table) {
            $table->dropColumn(['exchange_amount', 'original_pos_id']);
        });
    }
};
