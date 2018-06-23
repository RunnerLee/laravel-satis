<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    protected $fillable = [
        'name',
        'url',
        'last_build_at',
    ];

    protected $dates = ['last_build_at'];

    public function scopeByUrl($query, $url)
    {
        return $query->where('url', $url);
    }

    public function updateLastBuildTime()
    {
        $this->last_build_at = Carbon::now();

        $this->save();
    }
}
