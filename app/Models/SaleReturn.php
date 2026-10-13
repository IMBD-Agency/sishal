<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $fillable = [
        'customer_id',
        'pos_sale_id',
        'invoice_id',
        'return_date',
        'status',
        'refund_type',
        'reason',
        'processed_by',
        'processed_at',
        'account_id',
        'return_to_type',
        'return_to_id',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
    public function posSale()
    {
        return $this->belongsTo(\App\Models\Pos::class, 'pos_sale_id');
    }
    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Models\SaleReturnItem::class);
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'return_to_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'return_to_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class, 'return_to_id');
    }

    /**
     * Calculate total return value including proportional VAT
     */
    public function calculateTotalAmount(): float
    {
        $itemTotal = (float) $this->items->sum('total_price');
        $posSale = $this->posSale ?? ($this->invoice ? $this->invoice->pos : null);

        if ($posSale && $itemTotal > 0) {
            $vatRate = ($posSale->vat_rate > 0) 
                ? ($posSale->vat_rate / 100) 
                : ((($posSale->sub_total - $posSale->discount) > 0) ? ($posSale->vat_amount / ($posSale->sub_total - $posSale->discount)) : 0);

            $vat = round($itemTotal * $vatRate, 2);
            return round($itemTotal + $vat, 2);
        }

        return round($itemTotal, 2);
    }

    /**
     * Calculate proportional VAT amount on returned items
     */
    public function calculateVatAmount(): float
    {
        $itemTotal = (float) $this->items->sum('total_price');
        $posSale = $this->posSale ?? ($this->invoice ? $this->invoice->pos : null);

        if ($posSale && $itemTotal > 0) {
            $vatRate = ($posSale->vat_rate > 0) 
                ? ($posSale->vat_rate / 100) 
                : ((($posSale->sub_total - $posSale->discount) > 0) ? ($posSale->vat_amount / ($posSale->sub_total - $posSale->discount)) : 0);

            return round($itemTotal * $vatRate, 2);
        }

        return 0.0;
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->calculateTotalAmount();
    }
}
