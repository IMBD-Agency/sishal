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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('note');
            $table->text('gateway_response')->nullable()->after('status');
            $table->string('transaction_id')->nullable()->after('gateway_response');
            $table->string('payment_reference')->nullable()->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['status', 'gateway_response', 'transaction_id', 'payment_reference']);
        });
    }
};
