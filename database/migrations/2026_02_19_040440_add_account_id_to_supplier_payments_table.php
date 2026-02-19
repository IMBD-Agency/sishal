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
        if (!Schema::hasColumn('supplier_payments', 'account_id')) {
            Schema::table('supplier_payments', function (Blueprint $table) {
                $table->unsignedBigInteger('account_id')->nullable()->after('payment_method');
                $table->index('account_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropIndex(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
