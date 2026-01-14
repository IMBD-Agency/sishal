<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'zip_code',
        'company_name',
        'tax_number',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }

    public function bills()
    {
        return $this->hasMany(PurchaseBill::class, 'supplier_id');
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class, 'supplier_id');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(SupplierLedger::class, 'supplier_id')->orderBy('date')->orderBy('id');
    }

    public function getBalanceAttribute()
    {
        return $this->ledgerEntries()->latest('id')->first()?->balance ?? 0;
    }
}

