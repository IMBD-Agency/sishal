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
        // List of districts that should be treated as "Outside Dhaka" for delivery charges
        $outsideDhakaDistricts = [
            'Gazipur',
            'Narsingdi',
            'Narayanganj',
            'Tangail',
            'Kishoreganj',
            'Manikganj',
            'Munshiganj',
            'Rajbari',
            'Faridpur',
            'Gopalganj',
            'Madaripur',
            'Shariatpur',
        ];

        // Update state from 'Dhaka' to 'Outside Dhaka' for these districts
        DB::table('cities')
            ->whereIn('name', $outsideDhakaDistricts)
            ->where('state', 'Dhaka')
            ->where('country', 'Bangladesh')
            ->update(['state' => 'Outside Dhaka']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert state back to 'Dhaka' for these districts
        $outsideDhakaDistricts = [
            'Gazipur',
            'Narsingdi',
            'Narayanganj',
            'Tangail',
            'Kishoreganj',
            'Manikganj',
            'Munshiganj',
            'Rajbari',
            'Faridpur',
            'Gopalganj',
            'Madaripur',
            'Shariatpur',
        ];

        DB::table('cities')
            ->whereIn('name', $outsideDhakaDistricts)
            ->where('state', 'Outside Dhaka')
            ->where('country', 'Bangladesh')
            ->update(['state' => 'Dhaka']);
    }
};
