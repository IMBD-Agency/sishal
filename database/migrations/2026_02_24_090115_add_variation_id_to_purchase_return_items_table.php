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
        Schema::table('purchase_return_items', function (Blueprint $table) {
            // Add variation_id column (after product_id)
            if (!Schema::hasColumn('purchase_return_items', 'variation_id')) {
                $table->unsignedBigInteger('variation_id')->nullable()->after('product_id');
            }

            // Make purchase_item_id nullable (it might not always exist)
            if (Schema::hasColumn('purchase_return_items', 'purchase_item_id')) {
                $table->unsignedBigInteger('purchase_item_id')->nullable()->change();
            }

            // Make return_from_type nullable (allow NULL before location is chosen)
            if (Schema::hasColumn('purchase_return_items', 'return_from_type')) {
                // Change enum to nullable string so we don't have to drop/recreate the column
                $table->string('return_from_type', 20)->nullable()->change();
            }

            // Make return_from_id nullable
            if (Schema::hasColumn('purchase_return_items', 'return_from_id')) {
                $table->unsignedBigInteger('return_from_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_return_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_return_items', 'variation_id')) {
                $table->dropColumn('variation_id');
            }
        });
    }
};
