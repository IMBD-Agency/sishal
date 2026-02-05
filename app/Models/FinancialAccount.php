<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{
    protected $fillable = [
        'account_id',
        'type',
        'provider_name',
        'account_number',
        'account_holder_name',
        'currency',
        'branch_name',
        'swift_code',
        'mobile_number'
    ];

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'financial_account_id');
    }
}
