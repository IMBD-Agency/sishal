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
        // Sale Returns
        Schema::table('sale_returns', function (Blueprint $table) {
            $this->addIndexIfNotExist('sale_returns', 'pos_sale_id', $table);
            $this->addIndexIfNotExist('sale_returns', 'customer_id', $table);
            $this->addIndexIfNotExist('sale_returns', 'return_date', $table);
            $this->addIndexIfNotExist('sale_returns', 'status', $table);
        });

        // Sale Return Items
        Schema::table('sale_return_items', function (Blueprint $table) {
            $this->addIndexIfNotExist('sale_return_items', 'sale_return_id', $table);
            $this->addIndexIfNotExist('sale_return_items', 'product_id', $table);
            $this->addIndexIfNotExist('sale_return_items', 'variation_id', $table);
        });

        // Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $this->addIndexIfNotExist('invoices', 'customer_id', $table);
            $this->addIndexIfNotExist('invoices', 'issue_date', $table);
            $this->addIndexIfNotExist('invoices', 'status', $table);
        });
    }

    private function addIndexIfNotExist($table, $column, $blueprint)
    {
        $conn = DB::connection();
        $dbName = $conn->getDatabaseName();
        $indexName = "{$table}_{$column}_index";

        $exists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = ? 
            AND index_name = ?
        ", [$dbName, $table, $indexName])[0]->count;

        if (!$exists) {
            $blueprint->index($column);
        }
    }

    public function down(): void
    {
        // No-op for safety in partial failure scenarios
    }
};
