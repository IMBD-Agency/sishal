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
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('sender_account_id')->nullable()->after('due_amount');
            $table->unsignedBigInteger('receiver_account_id')->nullable()->after('sender_account_id');
            
            $table->foreign('sender_account_id')->references('id')->on('financial_accounts')->onDelete('set null');
            $table->foreign('receiver_account_id')->references('id')->on('financial_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['sender_account_id']);
            $table->dropForeign(['receiver_account_id']);
            $table->dropColumn(['sender_account_id', 'receiver_account_id']);
        });
    }
};
