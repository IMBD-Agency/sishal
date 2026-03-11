<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SanitizesHtml;

class Vlog extends Model
{
    use SanitizesHtml;

    protected $sanitizable = ['frame_code'];
    protected $fillable = [
        'frame_code',
        'is_featured',
        'is_active',
    ];
}
