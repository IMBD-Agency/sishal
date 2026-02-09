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
        Schema::table('journals', function (Blueprint $table) {
            $table->index('entry_date');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index('chart_of_account_id');
            $table->index('debit');
            $table->index('credit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropIndex(['entry_date']);
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex(['chart_of_account_id']);
            $table->dropIndex(['debit']);
            $table->dropIndex(['credit']);
        });
    }
};
