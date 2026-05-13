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
        Schema::table('pos_items', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_item_id')->nullable()->after('id');
            $table->index('parent_item_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_item_id')->nullable()->after('id');
            $table->index('parent_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_items', function (Blueprint $table) {
            $table->dropIndex(['parent_item_id']);
            $table->dropColumn('parent_item_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['parent_item_id']);
            $table->dropColumn('parent_item_id');
        });
    }
};
