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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands');
            $table->foreignId('season_id')->nullable()->after('brand_id')->constrained('seasons');
            $table->foreignId('gender_id')->nullable()->after('season_id')->constrained('genders');
            $table->foreignId('unit_id')->nullable()->after('gender_id')->constrained('units');
            $table->decimal('wholesale_price', 12, 2)->nullable()->after('price');
            $table->integer('alert_quantity')->nullable()->after('manage_stock');
            $table->string('style_number')->nullable()->after('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['season_id']);
            $table->dropForeign(['gender_id']);
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['brand_id', 'season_id', 'gender_id', 'unit_id', 'wholesale_price', 'alert_quantity', 'style_number']);
        });
    }
};
