<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationDetail extends Model
{
    protected $fillable = [
        'location_id',
        'photo_url'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}

