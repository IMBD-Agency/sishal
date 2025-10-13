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
        // First, update any existing 'service' records to 'product'
        DB::table('products')->where('type', 'service')->update(['type' => 'product']);
        
        // Then modify the enum constraint
        Schema::table('products', function (Blueprint $table) {
            $table->enum('type', ['product'])->default('product')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('type', ['product', 'service'])->default('product')->change();
        });
    }
};
