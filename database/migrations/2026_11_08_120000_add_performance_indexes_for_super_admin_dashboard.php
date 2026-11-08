<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to add indexes for performance optimization.
     */
    public function up(): void
    {
        // 1. Payments Table
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $this->addIndexIfNotExist('payments', 'payment_date', $table);
                $this->addIndexIfNotExist('payments', 'pos_id', $table);
                $this->addIndexIfNotExist('payments', 'invoice_id', $table);
                $this->addIndexIfNotExist('payments', 'account_id', $table);
                $this->addIndexIfNotExist('payments', 'payment_for', $table);
            });
        }

        // 2. POS Exchanges Tables
        if (Schema::hasTable('pos_exchanges')) {
            Schema::table('pos_exchanges', function (Blueprint $table) {
                $this->addIndexIfNotExist('pos_exchanges', 'exchange_date', $table);
                $this->addIndexIfNotExist('pos_exchanges', 'branch_id', $table);
                $this->addIndexIfNotExist('pos_exchanges', 'status', $table);
                $this->addIndexIfNotExist('pos_exchanges', 'original_pos_id', $table);
            });
        }

        if (Schema::hasTable('pos_exchange_items')) {
            Schema::table('pos_exchange_items', function (Blueprint $table) {
                $this->addIndexIfNotExist('pos_exchange_items', 'type', $table);
                $this->addIndexIfNotExist('pos_exchange_items', 'product_id', $table);
            });
        }

        // 3. Sale Returns Tables
        if (Schema::hasTable('sale_returns')) {
            Schema::table('sale_returns', function (Blueprint $table) {
                $this->addIndexIfNotExist('sale_returns', 'return_to_id', $table);
                $this->addIndexIfNotExist('sale_returns', 'invoice_id', $table);
            });
        }

        // 4. Journals and Journal Entries
        if (Schema::hasTable('journals')) {
            Schema::table('journals', function (Blueprint $table) {
                $this->addIndexIfNotExist('journals', 'branch_id', $table);
            });
        }

        if (Schema::hasTable('journal_entries')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $this->addIndexIfNotExist('journal_entries', 'journal_id', $table);
            });
        }

        // 5. Salary Payments
        if (Schema::hasTable('salary_payments')) {
            Schema::table('salary_payments', function (Blueprint $table) {
                $this->addIndexIfNotExist('salary_payments', 'payment_date', $table);
                $this->addIndexIfNotExist('salary_payments', 'branch_id', $table);
            });
        }
    }

    private function addIndexIfNotExist($table, $column, $blueprint)
    {
        try {
            $conn = DB::connection();
            $dbName = $conn->getDatabaseName();
            $indexName = "{$table}_{$column}_index";

            $exists = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.statistics 
                WHERE table_schema = ? 
                AND table_name = ? 
                AND index_name = ?
            ", [$dbName, $table, $indexName])[0]->count ?? 0;

            if (!$exists) {
                $blueprint->index($column, $indexName);
            }
        } catch (\Throwable $e) {
            // Silently ignore if check is unsupported or already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op for indexes
    }
};

