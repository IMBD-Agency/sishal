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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('ecommerce_source_type')->nullable()->comment('branch or warehouse');
            $table->unsignedBigInteger('ecommerce_source_id')->nullable()->comment('ID of the branch or warehouse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['ecommerce_source_type', 'ecommerce_source_id']);
        });
    }
};
