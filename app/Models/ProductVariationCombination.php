<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariationCombination extends Model
{
    protected $fillable = [
        'variation_id',
        'attribute_id',
        'attribute_value_id'
    ];

    /**
     * Get the variation that owns the combination.
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    /**
     * Get the attribute.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(VariationAttribute::class, 'attribute_id');
    }

    /**
     * Get the attribute value.
     */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(VariationAttributeValue::class, 'attribute_value_id');
    }
}
