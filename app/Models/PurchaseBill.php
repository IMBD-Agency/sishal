<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseBill extends Model
{
    protected $fillable = [
        'supplier_id',
        'purchase_id',
        'bill_number',
        'bill_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
        'created_by',
        'description',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($bill) {
            if (empty($bill->bill_number)) {
                $latest = static::latest('id')->first();
                $id = $latest ? $latest->id + 1 : 1;
                $bill->bill_number = 'PB-' . str_pad($id, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ledger()
    {
        return $this->morphOne(SupplierLedger::class, 'transactionable');
    }
}

