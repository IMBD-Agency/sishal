<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariationGallery extends Model
{
    protected $fillable = [
        'variation_id',
        'image',
        'alt_text',
        'sort_order'
    ];

    protected static function booted()
    {
        static::deleting(function ($gallery) {
            if ($gallery->image && file_exists(public_path($gallery->image))) {
                @unlink(public_path($gallery->image));
            }
        });
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    /**
     * Scope for ordered gallery images.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
