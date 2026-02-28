<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$p = Product::find(9);
dump([
    'name' => $p->name,
    'has_variations' => $p->has_variations,
    'hasStock' => $p->hasStock(),
    'total_variation_stock' => $p->total_variation_stock,
    'warehouseStock' => $p->warehouseStock()->sum('quantity'),
    'activeBranchStock' => $p->branchStock()->whereHas('branch', function($q){ $q->where('show_online', true); })->sum('quantity')
]);
