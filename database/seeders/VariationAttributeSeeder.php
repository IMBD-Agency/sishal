<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VariationAttribute;
use App\Models\VariationAttributeValue;

class VariationAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Color attribute
        $colorAttribute = VariationAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'description' => 'Product color variations',
            'is_required' => true,
            'is_color' => true,
            'sort_order' => 1,
            'status' => 'active'
        ]);

        // Create Size attribute
        $sizeAttribute = VariationAttribute::create([
            'name' => 'Size',
            'slug' => 'size',
            'description' => 'Product size variations',
            'is_required' => true,
            'is_color' => false,
            'sort_order' => 2,
            'status' => 'active'
        ]);

        // Create Color values
        $colors = [
            ['value' => 'Red', 'color_code' => '#FF0000', 'sort_order' => 1],
            ['value' => 'Blue', 'color_code' => '#0000FF', 'sort_order' => 2],
            ['value' => 'Green', 'color_code' => '#008000', 'sort_order' => 3],
            ['value' => 'Black', 'color_code' => '#000000', 'sort_order' => 4],
            ['value' => 'White', 'color_code' => '#FFFFFF', 'sort_order' => 5],
            ['value' => 'Yellow', 'color_code' => '#FFFF00', 'sort_order' => 6],
            ['value' => 'Purple', 'color_code' => '#800080', 'sort_order' => 7],
            ['value' => 'Orange', 'color_code' => '#FFA500', 'sort_order' => 8],
            ['value' => 'Pink', 'color_code' => '#FFC0CB', 'sort_order' => 9],
            ['value' => 'Gray', 'color_code' => '#808080', 'sort_order' => 10],
        ];

        foreach ($colors as $color) {
            VariationAttributeValue::create([
                'attribute_id' => $colorAttribute->id,
                'value' => $color['value'],
                'color_code' => $color['color_code'],
                'sort_order' => $color['sort_order'],
                'status' => 'active'
            ]);
        }

        // Create Size values
        $sizes = [
            ['value' => 'XS', 'sort_order' => 1],
            ['value' => 'S', 'sort_order' => 2],
            ['value' => 'M', 'sort_order' => 3],
            ['value' => 'L', 'sort_order' => 4],
            ['value' => 'XL', 'sort_order' => 5],
            ['value' => 'XXL', 'sort_order' => 6],
            ['value' => 'XXXL', 'sort_order' => 7],
        ];

        foreach ($sizes as $size) {
            VariationAttributeValue::create([
                'attribute_id' => $sizeAttribute->id,
                'value' => $size['value'],
                'sort_order' => $size['sort_order'],
                'status' => 'active'
            ]);
        }
    }
}
