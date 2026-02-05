<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'voucher_no', 
        'type', 
        'entry_date', 
        'description', 
        'branch_id', 
        'customer_id', 
        'supplier_id', 
        'expense_account_id', 
        'voucher_amount', 
        'paid_amount', 
        'reference',
        'created_by', 
        'updated_by'
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function entries()
    {
        return $this->hasMany(JournalEntry::class, 'journal_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updator()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function expenseAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'expense_account_id');
    }

    // Helper to calculate totals
    public function getTotalDebitAttribute()
    {
        return $this->entries->sum('debit');
    }

    public function getTotalCreditAttribute()
    {
        return $this->entries->sum('credit');
    }

    public function isBalanced()
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }
}
