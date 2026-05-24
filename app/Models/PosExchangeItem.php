<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosExchangeItem extends Model
{
    protected $guarded = ['id'];

    public function exchange()
    {
        return $this->belongsTo(PosExchange::class, 'pos_exchange_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class);
    }
}
