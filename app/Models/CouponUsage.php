<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $fillable = [
        'coupon_id',
        'user_id',
        'session_id',
        'order_id',
        'discount_amount',
        'order_total',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'order_total' => 'decimal:2',
    ];

    /**
     * Get the coupon
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
