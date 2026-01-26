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
        if (!Schema::hasColumn('branches', 'status')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->string('status')->default('active')->after('contact_info');
            });
        }
        if (!Schema::hasColumn('branches', 'show_online')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->boolean('show_online')->default(true)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['status', 'show_online']);
        });
    }
};
