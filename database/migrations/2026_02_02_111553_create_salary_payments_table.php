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
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('month');
            $table->integer('year');
            $table->decimal('total_salary', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('account_no')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            // We use indexes instead of formal foreign keys to avoid engine mismatch issues (MyISAM vs InnoDB)
            $table->index('employee_id');
            $table->index('branch_id');
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
