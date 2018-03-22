<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    protected $fillable = [
        'name',
        'url',
        'build_at',
    ];

    protected $dates = ['build_at'];

    public function scopeByUrl($query, $url)
    {
        return $query->where('url', $url);
    }
}
