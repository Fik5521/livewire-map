<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use Illuminate\Support\Facades\File;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil data dari file JSON
        $path = database_path('seeders/locations.json');
        $json = File::get($path);
        $data = json_decode($json, true);

        // 2. Bersihkan tabel sebelum mengisi (Opsional)
        // Location::truncate(); 

        // 3. Masukkan data ke MySQL
        foreach ($data['features'] as $feature) {
            Location::create([
                'name'    => $feature['properties']['NAMOBJ'],
                'address' => $feature['properties']['ALMIND'] ?? null,
                'fcode'   => $feature['properties']['FCODE'] ?? null,
                'radius'  => 150, // Default radius dalam meter
                'lng'     => $feature['geometry']['coordinates'][0],
                'lat'     => $feature['geometry']['coordinates'][1],
            ]);
        }

        $this->command->info("Berhasil memindahkan data ke MySQL!");
    }
}
