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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('contact_phone')->nullable()->after('location');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['contact_phone', 'contact_email', 'status']);
        });
    }
};
