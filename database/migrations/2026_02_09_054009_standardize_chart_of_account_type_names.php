<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Standardize all Chart of Account Type names to SINGULAR form
        DB::table('chart_of_account_types')->where('name', 'Assets')->update(['name' => 'Asset']);
        DB::table('chart_of_account_types')->where('name', 'Liabilities')->update(['name' => 'Liability']);
        DB::table('chart_of_account_types')->where('name', 'Equities')->update(['name' => 'Equity']);
        DB::table('chart_of_account_types')->whereIn('name', ['Revenues', 'Income'])->update(['name' => 'Revenue']);
        DB::table('chart_of_account_types')->where('name', 'Expenses')->update(['name' => 'Expense']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to plural form (if needed for rollback)
        DB::table('chart_of_account_types')->where('name', 'Asset')->update(['name' => 'Assets']);
        DB::table('chart_of_account_types')->where('name', 'Liability')->update(['name' => 'Liabilities']);
        DB::table('chart_of_account_types')->where('name', 'Equity')->update(['name' => 'Equities']);
        DB::table('chart_of_account_types')->where('name', 'Revenue')->update(['name' => 'Revenues']);
        DB::table('chart_of_account_types')->where('name', 'Expense')->update(['name' => 'Expenses']);
    }
};
