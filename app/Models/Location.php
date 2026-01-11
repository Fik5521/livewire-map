<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LocationDetail;


class Location extends Model
{
    // Pastikan semua field ini ada agar bisa masuk ke MySQL
    protected $fillable = ['name', 'kecamatan', 'address', 'fcode', 'radius', 'lat', 'lng'];

    public function details()
    {
        return $this->hasMany(LocationDetail::class);
    }
}
