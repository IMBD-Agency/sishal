<?php

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\JournalEntry;
use App\Models\Journal;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- DEBUG START ---\n";

// 1. Check Account Types
$revTypes = ChartOfAccountType::whereIn('name', ['Revenue', 'Income'])->get();
$expTypes = ChartOfAccountType::whereIn('name', ['Expenses', 'Expense'])->get();

echo "Revenue Types Found: " . $revTypes->pluck('name')->implode(', ') . "\n";
echo "Expense Types Found: " . $expTypes->pluck('name')->implode(', ') . "\n";

// 2. Check Accounts linked to these types
$revCount = ChartOfAccount::whereIn('type_id', $revTypes->pluck('id'))->count();
$expCount = ChartOfAccount::whereIn('type_id', $expTypes->pluck('id'))->count();
echo "Revenue Accounts: $revCount\n";
echo "Expense Accounts: $expCount\n";

// 3. Check Entries for 2026-02-01
$date = '2026-02-01';
$entries = JournalEntry::whereHas('journal', function($q) use ($date) {
    $q->whereDate('entry_date', $date);
})->get();

echo "Entries on $date: " . $entries->count() . "\n";

if ($entries->count() > 0) {
    echo "Sample Entry Account: " . $entries->first()->chartOfAccount->name . 
         " (Type ID: " . $entries->first()->chartOfAccount->type_id . ")\n";
}

echo "--- DEBUG END ---\n";
