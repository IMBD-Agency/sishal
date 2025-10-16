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
        // Add indexes only if they don't exist
        try {
            Schema::table('products', function (Blueprint $table) {
                // Check if indexes exist before adding
                if (!$this->indexExists('products', 'products_type_status_created_idx')) {
                    $table->index(['type', 'status', 'created_at'], 'products_type_status_created_idx');
                }
                if (!$this->indexExists('products', 'products_type_status_idx')) {
                    $table->index(['type', 'status'], 'products_type_status_idx');
                }
                if (!$this->indexExists('products', 'products_status_created_idx')) {
                    $table->index(['status', 'created_at'], 'products_status_created_idx');
                }
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('pos_items', function (Blueprint $table) {
                if (!$this->indexExists('pos_items', 'pos_items_product_created_idx')) {
                    $table->index(['product_id', 'created_at'], 'pos_items_product_created_idx');
                }
                if (!$this->indexExists('pos_items', 'pos_items_product_quantity_idx')) {
                    $table->index(['product_id', 'quantity'], 'pos_items_product_quantity_idx');
                }
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('order_items', function (Blueprint $table) {
                if (!$this->indexExists('order_items', 'order_items_product_created_idx')) {
                    $table->index(['product_id', 'created_at'], 'order_items_product_created_idx');
                }
                if (!$this->indexExists('order_items', 'order_items_product_quantity_idx')) {
                    $table->index(['product_id', 'quantity'], 'order_items_product_quantity_idx');
                }
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('wishlists', function (Blueprint $table) {
                if (!$this->indexExists('wishlists', 'wishlists_user_product_idx')) {
                    $table->index(['user_id', 'product_id'], 'wishlists_user_product_idx');
                }
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('reviews', function (Blueprint $table) {
                if (!$this->indexExists('reviews', 'reviews_product_approved_idx')) {
                    $table->index(['product_id', 'is_approved'], 'reviews_product_approved_idx');
                }
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists($table, $indexName)
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table}");
        foreach ($indexes as $index) {
            if ($index->Key_name === $indexName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_type_status_created_idx');
            $table->dropIndex('products_type_status_idx');
            $table->dropIndex('products_status_created_idx');
        });

        Schema::table('pos_items', function (Blueprint $table) {
            $table->dropIndex('pos_items_product_created_idx');
            $table->dropIndex('pos_items_product_quantity_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_product_created_idx');
            $table->dropIndex('order_items_product_quantity_idx');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropIndex('wishlists_user_product_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_product_approved_idx');
        });
    }
};