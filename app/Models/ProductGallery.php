<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGallery extends Model
{
    protected $fillable = [
        'product_id', 'image'
    ];

    protected static function booted()
    {
        static::deleting(function ($gallery) {
            if ($gallery->image && file_exists(public_path($gallery->image))) {
                @unlink(public_path($gallery->image));
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
