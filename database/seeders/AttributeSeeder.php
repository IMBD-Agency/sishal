<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Flow Rate',
                'slug' => 'flow-rate',
                'description' => 'Water flow rate specification',
                'status' => 'active'
            ],
            [
                'name' => 'Voltage',
                'slug' => 'voltage',
                'description' => 'Electrical voltage requirement',
                'status' => 'active'
            ],
            [
                'name' => 'Power Consumption',
                'slug' => 'power-consumption',
                'description' => 'Power consumption in watts',
                'status' => 'active'
            ],
            [
                'name' => 'Operating Pressure',
                'slug' => 'operating-pressure',
                'description' => 'Operating pressure range',
                'status' => 'active'
            ],
            [
                'name' => 'Inlet/Outlet Size',
                'slug' => 'inlet-outlet-size',
                'description' => 'Connection size specifications',
                'status' => 'active'
            ],
            [
                'name' => 'Dimensions',
                'slug' => 'dimensions',
                'description' => 'Product physical dimensions',
                'status' => 'active'
            ],
            [
                'name' => 'Weight',
                'slug' => 'weight',
                'description' => 'Product weight',
                'status' => 'active'
            ],
            [
                'name' => 'Material',
                'slug' => 'material',
                'description' => 'Construction material',
                'status' => 'active'
            ],
            [
                'name' => 'Operating Temperature',
                'slug' => 'operating-temperature',
                'description' => 'Temperature operating range',
                'status' => 'active'
            ],
            [
                'name' => 'Warranty',
                'slug' => 'warranty',
                'description' => 'Warranty period and terms',
                'status' => 'active'
            ],
            [
                'name' => 'Color',
                'slug' => 'color',
                'description' => 'Product color options',
                'status' => 'active'
            ],
            [
                'name' => 'Size',
                'slug' => 'size',
                'description' => 'Product size options',
                'status' => 'active'
            ],
            [
                'name' => 'Brand',
                'slug' => 'brand',
                'description' => 'Product brand',
                'status' => 'active'
            ],
            [
                'name' => 'Model',
                'slug' => 'model',
                'description' => 'Product model number',
                'status' => 'active'
            ],
            [
                'name' => 'Country of Origin',
                'slug' => 'country-of-origin',
                'description' => 'Manufacturing country',
                'status' => 'active'
            ]
        ];

        foreach ($attributes as $attribute) {
            Attribute::create($attribute);
        }
    }
}
