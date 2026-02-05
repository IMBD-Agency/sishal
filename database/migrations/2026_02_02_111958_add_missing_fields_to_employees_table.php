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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('employees', 'salary')) {
                $table->decimal('salary', 15, 2)->default(0)->after('address');
            }
            if (!Schema::hasColumn('employees', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('salary');
            }
            // Designation already exists in DB as seen in DESCRIBE
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['address', 'salary', 'hire_date']);
        });
    }
};
