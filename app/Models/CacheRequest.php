<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CacheRequest extends Model
{
    protected $fillable = [
        'url',
        'method',
        'status',
        'http_status',
        'response_time',
        'response_size',
        'content_type',
    ];
}