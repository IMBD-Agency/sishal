<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- ENTITY CHECK ---\n";
$p = \App\Models\Purchase::find(4);
if ($p) {
    echo "Purchase 4: FOUND\n";
    echo "Supplier ID: " . ($p->supplier_id ?? 'NULL') . "\n";
    if ($p->supplier) echo "Supplier Name: " . $p->supplier->name . "\n";
    echo "Bill ID: " . ($p->bill_id ?? 'NULL') . "\n";
    if ($p->bill) echo "Bill Number: " . $p->bill->bill_number . "\n";
} else {
    echo "Purchase 4: NOT FOUND\n";
}

$prod = \App\Models\Product::find(2);
if ($prod) {
    echo "Product 2: FOUND\n";
    echo "Name: " . $prod->name . "\n";
    echo "Category: " . ($prod->category ? $prod->category->name : 'NULL') . "\n";
    echo "Brand: " . ($prod->brand ? $prod->brand->name : 'NULL') . "\n";
} else {
    echo "Product 2: NOT FOUND\n";
}




