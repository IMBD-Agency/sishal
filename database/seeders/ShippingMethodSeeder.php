<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ShippingMethod;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shippingMethods = [
            [
                'name' => 'Standard Shipping',
                'description' => 'Regular delivery service',
                'cost' => 60.00,
                'estimated_days_min' => 5,
                'estimated_days_max' => 7,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Express Shipping',
                'description' => 'Fast delivery service',
                'cost' => 100.00,
                'estimated_days_min' => 2,
                'estimated_days_max' => 3,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Overnight Shipping',
                'description' => 'Next business day delivery',
                'cost' => 120.00,
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($shippingMethods as $method) {
            ShippingMethod::create($method);
        }
    }
}
