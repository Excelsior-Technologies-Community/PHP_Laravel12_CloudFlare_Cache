<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalCacheEntry extends Model
{
    protected $fillable = [
        'cache_key',
        'url',
        'http_status',
        'response_time',
        'response_size',
        'content_type',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'http_status' => 'integer',
        'response_time' => 'float',
        'response_size' => 'integer',
    ];
}