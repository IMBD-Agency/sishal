<?php

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- DEBUG PARENT TYPE START ---\n";

// Get all accounts and check their structure
$accounts = ChartOfAccount::with('parent.type')->take(5)->get();

foreach($accounts as $acc) {
    echo "Account: " . $acc->name . "\n";
    echo " - Direct Type ID: " . ($acc->type_id ?? 'NULL') . "\n";
    echo " - Parent: " . ($acc->parent ? $acc->parent->name : 'None') . "\n";
    if ($acc->parent) {
        echo " - Parent Type ID: " . ($acc->parent->type_id ?? 'NULL') . "\n";
        echo " - Parent Type Name: " . ($acc->parent->type ? $acc->parent->type->name : 'N/A') . "\n";
    }
    echo "-------------------\n";
}

// Check if any accounts inherit type from parent that matches Revenue/Expense
$revTypes = ChartOfAccountType::whereIn('name', ['Revenue', 'Income'])->pluck('id');

$accountsViaParent = ChartOfAccount::whereHas('parent', function($q) use ($revTypes) {
    $q->whereIn('type_id', $revTypes);
})->count();

echo "Accounts with Revenue Parent: $accountsViaParent\n";

echo "--- DEBUG END ---\n";
