<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = DB::table('journal_entries')
    ->join('journals','journal_entries.journal_id','=','journals.id')
    ->join('chart_of_accounts','journal_entries.chart_of_account_id','=','chart_of_accounts.id')
    ->whereDate('journals.entry_date', today())
    ->select('chart_of_accounts.name as acct','journal_entries.debit','journal_entries.credit','journals.description', 'journals.type')
    ->get();

foreach ($rows as $r) {
    echo $r->description . ' | type:' . $r->type . ' | acct:' . $r->acct . ' | D:' . $r->debit . ' C:' . $r->credit . PHP_EOL;
}
echo PHP_EOL . "Total rows: " . count($rows) . PHP_EOL;

// Also check sale_returns table
$returns = DB::table('sale_returns')->whereDate('return_date', today())->get();
echo PHP_EOL . "Sale returns today: " . count($returns) . PHP_EOL;
foreach ($returns as $r) {
    echo "ID:".$r->id." pos_sale_id:".$r->pos_sale_id." refund_type:".$r->refund_type." status:".$r->status . PHP_EOL;
}
