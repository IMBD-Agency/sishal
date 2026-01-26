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
            $table->decimal('unit_price', 12, 2)->default(0)->after('quantity');
            $table->decimal('total_price', 12, 2)->default(0)->after('unit_price');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total_price');
            $table->decimal('due_amount', 12, 2)->default(0)->after('paid_amount');
            $table->string('sender_account_type')->nullable()->after('due_amount');
            $table->string('sender_account_number')->nullable()->after('sender_account_type');
            $table->string('receiver_account_type')->nullable()->after('sender_account_number');
            $table->string('receiver_account_number')->nullable()->after('receiver_account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropColumn([
                'unit_price', 
                'total_price', 
                'paid_amount', 
                'due_amount',
                'sender_account_type',
                'sender_account_number',
                'receiver_account_type',
                'receiver_account_number'
            ]);
        });
    }
};
