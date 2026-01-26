<?php

/**
 * Migration Sync Script
 * This script marks migrations as "ran" without actually executing them
 * Use this when your database structure is already up to date but Laravel doesn't know about it
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// List of migrations that exist in your database structure but aren't tracked
// These are the migrations between your last tracked one and the current state
$migrationsToMark = [
    '2025_11_09_084537_add_type_and_value_to_bulk_discounts_table',
    '2025_11_10_085613_add_free_delivery_to_coupons_table',
    '2025_11_10_093510_add_cod_percentage_to_general_settings_table',
    '2025_11_12_110836_add_features_to_products_table',
    '2025_11_12_115011_add_free_delivery_to_products_table',
    '2025_11_12_120257_add_free_delivery_to_bulk_discounts_table',
    '2025_11_12_122248_update_bulk_discounts_type_enum_to_include_free_delivery',
    '2025_11_13_054921_add_gtm_container_id_to_general_settings_table',
    '2025_12_24_051629_add_is_ecommerce_to_products_table',
    '2026_01_13_053115_add_extra_fields_to_products_table',
    '2026_01_13_103942_add_manual_sale_fields_to_pos_table',
    '2026_01_19_090950_add_discount_to_invoice_items_table',
    '2025_01_20_000000_add_variation_id_to_sale_return_items_table',
    '2025_01_20_000001_add_variation_id_to_stock_transfers_table',
    '2025_01_27_000000_add_telegram_username_to_general_settings_table',
    '2026_01_25_103639_add_status_and_show_online_to_branches_table',
];

echo "Starting migration sync...\n\n";

$batch = DB::table('migrations')->max('batch') + 1;

foreach ($migrationsToMark as $migration) {
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    
    if (!$exists) {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch
        ]);
        echo "✓ Marked as complete: {$migration}\n";
    } else {
        echo "○ Already tracked: {$migration}\n";
    }
}

echo "\n✅ Migration sync complete!\n";
echo "You can now run 'php artisan migrate' to apply any remaining new migrations.\n";
