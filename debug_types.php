<?php

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- ACCOUNT TYPES SUMMARY ---\n";

$types = ChartOfAccountType::all();

foreach($types as $type) {
    $count = ChartOfAccount::where('type_id', $type->id)->count();
    echo "ID: {$type->id} | Name: {$type->name} | Accounts: {$count}\n";
}

echo "--- PARENT TYPES SUMMARY ---\n";
// Check if accounts are linked via parents
foreach($types as $type) {
    $count = ChartOfAccount::whereHas('parent', function($q) use ($type) {
        $q->where('type_id', $type->id);
    })->count();
    if ($count > 0) {
        echo "Parent ID: {$type->id} | Name: {$type->name} | Accounts via Parent: {$count}\n";
    }
}
