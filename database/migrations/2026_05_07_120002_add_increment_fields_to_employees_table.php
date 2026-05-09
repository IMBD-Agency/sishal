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
            if (!Schema::hasColumn('employees', 'previous_salary')) {
                $table->decimal('previous_salary', 15, 2)->default(0)->after('salary');
            }
            if (!Schema::hasColumn('employees', 'increment_amount')) {
                $table->decimal('increment_amount', 15, 2)->default(0)->after('previous_salary');
            }
            if (!Schema::hasColumn('employees', 'increment_percentage')) {
                $table->decimal('increment_percentage', 5, 2)->default(0)->after('increment_amount');
            }
            if (!Schema::hasColumn('employees', 'increment_effective_date')) {
                $table->date('increment_effective_date')->nullable()->after('increment_percentage');
            }
            if (!Schema::hasColumn('employees', 'last_increment_date')) {
                $table->date('last_increment_date')->nullable()->after('increment_effective_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['previous_salary', 'increment_amount', 'increment_percentage', 'increment_effective_date', 'last_increment_date']);
        });
    }
};
