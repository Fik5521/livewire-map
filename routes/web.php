<?php

use App\Livewire\MapLocation;
use Illuminate\Support\Facades\Route;

Route::get('/', MapLocation::class);

Route::get('/locations-data', function () {
	return \App\Models\Location::select('id', 'name', 'lat', 'lng', 'kecamatan')->get();
});
