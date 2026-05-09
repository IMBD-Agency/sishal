<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'from_financial_account_id',
        'to_financial_account_id',
        'chart_of_account_id',
        'amount',
        'transfer_date',
        'reference',
        'memo',
        'journal_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_date' => 'date',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'from_financial_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(FinancialAccount::class, 'to_financial_account_id');
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFromLocationAttribute()
    {
        $account = $this->fromAccount;
        if (!$account) return 'Unknown';
        
        if ($account->branch_id) {
            return $account->branch->name ?? 'Branch';
        }
        if ($account->warehouse_id) {
            return $account->warehouse->name ?? 'Warehouse';
        }
        return $account->provider_name ?? 'Account';
    }

    public function getToLocationAttribute()
    {
        $account = $this->toAccount;
        if (!$account) return 'Unknown';
        
        if ($account->branch_id) {
            return $account->branch->name ?? 'Branch';
        }
        if ($account->warehouse_id) {
            return $account->warehouse->name ?? 'Warehouse';
        }
        return $account->provider_name ?? 'Account';
    }
}
