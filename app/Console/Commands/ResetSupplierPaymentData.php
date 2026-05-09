<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetSupplierPaymentData extends Command
{
    protected $signature = 'data:reset-supplier-payments {--all : Reset all ERP data}';
    protected $description = 'Reset supplier payment data for production start';

    public function handle()
    {
        $this->warn('This will DELETE all supplier payment data!');
        if (!$this->confirm('Are you sure?')) {
            return;
        }

        DB::beginTransaction();
        try {
            // 1. Delete Supplier Payments
            DB::table('supplier_payments')->delete();
            $this->info('✓ Supplier payments deleted');

            // 2. Reset Purchase Bills (set all to unpaid)
            DB::table('purchase_bills')->update([
                'paid_amount' => 0,
                'due_amount' => DB::raw('total_amount'),
                'status' => 'unpaid'
            ]);
            $this->info('✓ Purchase bills reset to unpaid');

            // 3. Delete Supplier Ledger
            DB::table('supplier_ledgers')->delete();
            $this->info('✓ Supplier ledger cleared');

            // 4. Delete Supplier Balances
            DB::table('balances')->where('source_type', 'supplier')->delete();
            $this->info('✓ Supplier balances cleared');

            // 5. Delete related journals
            DB::table('journals')->where('type', 'Payment')->whereNotNull('supplier_id')->delete();
            $this->info('✓ Journal entries cleared');

            DB::commit();
            $this->info('');
            $this->info('✅ All supplier payment data reset successfully!');
            $this->info('You can now start fresh for production.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
        }
    }
}

