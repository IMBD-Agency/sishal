<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccountSubType extends Model
{
    protected $fillable = ['type_id', 'name', 'description'];

    public function type()
    {
        return $this->belongsTo(ChartOfAccountType::class, 'type_id');
    }

    public function parents()
    {
        return $this->hasMany(ChartOfAccountParent::class, 'sub_type_id');
    }

    public function accounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'sub_type_id');
    }
}
