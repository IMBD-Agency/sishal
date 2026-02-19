<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'invoice_number',
        'from_type',
        'from_id',
        'to_type',
        'to_id',
        'product_id',
        'variation_id',
        'quantity',
        'unit_price',
        'total_price',
        'paid_amount',
        'due_amount',
        'sender_account_id',
        'receiver_account_id',
        'sender_account_type',
        'sender_account_number',
        'receiver_account_type',
        'receiver_account_number',
        'type',
        'status',
        'requested_by',
        'approved_by',
        'shipped_by',
        'delivered_by',
        'requested_at',
        'approved_at',
        'shipped_at',
        'delivered_at',
        'notes',
        'invoice_number',
    ];


    

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'from_id');
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class, 'from_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'to_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class, 'to_id');
    }

    public function requestedPerson()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function approvedPerson()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function variation()
    {
        return $this->belongsTo(\App\Models\ProductVariation::class, 'variation_id');
    }

    public function senderAccount()
    {
        return $this->belongsTo(\App\Models\FinancialAccount::class, 'sender_account_id');
    }

    public function receiverAccount()
    {
        return $this->belongsTo(\App\Models\FinancialAccount::class, 'receiver_account_id');
    }
}



