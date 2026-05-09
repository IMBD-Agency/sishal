<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\Balance;

class SyncSupplierBalances extends Command
{
    protected $signature = 'supplier:sync-balances';
    protected $description = 'Sync supplier balances from SupplierLedger to Balance model';

    public function handle()
    {
        $this->info('Starting supplier balance sync...');

        $suppliers = Supplier::all();
        $fixed = 0;

        foreach ($suppliers as $supplier) {
            // Get latest ledger balance
            $ledgerBalance = $supplier->ledgerEntries()->latest('id')->first()?->balance ?? 0;
            
            // Get or create Balance record
            $balance = Balance::where('source_type', 'supplier')
                ->where('source_id', $supplier->id)
                ->first();

            if (!$balance) {
                $balance = Balance::create([
                    'source_type' => 'supplier',
                    'source_id' => $supplier->id,
                    'balance' => $ledgerBalance,
                    'description' => 'Auto-synced from SupplierLedger',
                ]);
                $this->info("Created balance for Supplier #{$supplier->id}: {$ledgerBalance}");
            } else {
                $oldBalance = $balance->balance;
                $balance->balance = $ledgerBalance;
                $balance->save();
                
                if ($oldBalance != $ledgerBalance) {
                    $this->info("Updated Supplier #{$supplier->id}: {$oldBalance} → {$ledgerBalance}");
                    $fixed++;
                }
            }
        }

        $this->info("Sync complete! Fixed {$fixed} suppliers.");
        return 0;
    }
}
