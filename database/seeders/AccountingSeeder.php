<?php

namespace Database\Seeders;

use App\Models\ChartOfAccountType;
use App\Models\ChartOfAccountSubType;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Asset' => ['Current Assets', 'Fixed Assets', 'Inventory', 'Cash and Bank'],
            'Liability' => ['Current Liabilities', 'Long-term Liabilities', 'Accounts Payable'],
            'Equity' => ['Owner\'s Capital', 'Retained Earnings'],
            'Revenue' => ['Sales Revenue', 'Service Revenue', 'Other Income'],
            'Expense' => ['Cost of Goods Sold', 'Operating Expenses', 'Administrative Expenses', 'Marketing Expenses', 'Payroll Expenses'],
        ];

        foreach ($types as $typeName => $subTypes) {
            $type = ChartOfAccountType::create(['name' => $typeName]);
            foreach ($subTypes as $subTypeName) {
                ChartOfAccountSubType::create([
                    'name' => $subTypeName,
                    'type_id' => $type->id,
                ]);
            }
        }
    }
}
