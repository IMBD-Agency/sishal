<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{
    const TYPE_BANK = 'bank';
    const TYPE_MOBILE = 'mobile';
    const TYPE_CASH = 'cash';

    protected $fillable = [
        'account_id',
        'type',
        'provider_name',
        'account_number',
        'account_holder_name',
        'currency',
        'branch_name',
        'swift_code',
        'mobile_number',
        'balance'
    ];

    /**
     * Get all available account types.
     */
    public static function getTypes()
    {
        return [
            self::TYPE_CASH => 'Cash',
            self::TYPE_BANK => 'Bank Account',
            self::TYPE_MOBILE => 'Mobile Banking',
        ];
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'financial_account_id');
    }
}
