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
            $table->dropColumn([
                'service_charge_dhaka',
                'service_charge_outside',
                'service_contact_phone',
                'service_note_bangla'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->decimal('service_charge_dhaka', 10, 2)->default(300.00)->after('invoice_prefix');
            $table->decimal('service_charge_outside', 10, 2)->default(500.00)->after('service_charge_dhaka');
            $table->string('service_contact_phone')->nullable()->after('service_charge_outside');
            $table->text('service_note_bangla')->nullable()->after('service_contact_phone');
        });
    }
};