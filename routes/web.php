<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\MapLocation;
use App\Livewire\UserMapLocation;
use Spatie\Permission\Models\Role;


Route::get('/', UserMapLocation::class)->name('home');

//user
Route::middleware('role:user|admin')->group(function () {
    Route::get('/user', UserMapLocation::class)
        ->name('user.dashboard');

});

//admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', MapLocation::class)
        ->name('admin.dashboard');
});

Route::get('/locations-data', function () {
    return \App\Models\Location::select(
        'id',
        'name',
        'lat',
        'lng',
        'kecamatan'
    )->get();
});


require __DIR__ . '/auth.php';
