<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the findProductByStyle method
$controller = new App\Http\Controllers\Erp\ProductController();

// Find a product with style number
$product = App\Models\Product::where('style_number', '!=', '')->first();

if ($product) {
    echo "Testing with Style Number: " . $product->style_number . "\n";
    echo "Product ID: " . $product->id . "\n";
    echo "Has Variations Flag: " . ($product->has_variations ? 'Yes' : 'No') . "\n";
    echo "Actual Variations Count: " . $product->variations()->count() . "\n\n";
    
    // Call the controller method
    $response = $controller->findProductByStyle($product->style_number);
    $data = json_decode($response->getContent(), true);
    
    echo "API Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No products with style_number found in database\n";
}
