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
        // Map plural types to singular types
        $typeMapping = [
            'Assets' => 'Asset',
            'Liabilities' => 'Liability',
            'Expenses' => 'Expense',
            'Revenues' => 'Revenue',
            'Equities' => 'Equity',
            'Income' => 'Revenue',
        ];

        foreach ($typeMapping as $oldName => $newName) {
            // Find the old (plural) type
            $oldType = DB::table('chart_of_account_types')->where('name', $oldName)->first();
            
            if ($oldType) {
                // Find or create the new (singular) type
                $newType = DB::table('chart_of_account_types')->where('name', $newName)->first();
                
                if (!$newType) {
                    // If singular doesn't exist, just rename the plural one
                    DB::table('chart_of_account_types')
                        ->where('id', $oldType->id)
                        ->update(['name' => $newName]);
                } else {
                    // If both exist, migrate all references from old to new
                    
                    // Update chart_of_accounts
                    DB::table('chart_of_accounts')
                        ->where('type_id', $oldType->id)
                        ->update(['type_id' => $newType->id]);
                    
                    // Update chart_of_account_parents
                    DB::table('chart_of_account_parents')
                        ->where('type_id', $oldType->id)
                        ->update(['type_id' => $newType->id]);
                    
                    // Update chart_of_account_sub_types
                    DB::table('chart_of_account_sub_types')
                        ->where('type_id', $oldType->id)
                        ->update(['type_id' => $newType->id]);
                    
                    // Now delete the old duplicate type
                    DB::table('chart_of_account_types')
                        ->where('id', $oldType->id)
                        ->delete();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed - duplicates should stay removed
    }
};
