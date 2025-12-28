<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use Illuminate\Support\Facades\File;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil path file json
        $path = database_path('seeders/locations.json');
        
        // 2. Baca isi file
        $json = File::get($path);
        
        // 3. Decode menjadi array
        $data = json_decode($json, true);

        // 4. Validasi jika decode berhasil
        if (is_null($data) || !isset($data['features'])) {
            $this->command->error("Gagal membaca file JSON. Pastikan format JSON valid.");
            return;
        }

        $this->command->getOutput()->progressStart(count($data['features']));

        foreach ($data['features'] as $feature) {
            Location::create([
                'name'    => $feature['properties']['NAMOBJ'] ?? 'Tanpa Nama',
                'address' => $feature['properties']['ALMIND'] ?? null,
                'fcode'   => $feature['properties']['FCODE'] ?? null,
                'lng'     => $feature['geometry']['coordinates'][0],
                'lat'     => $feature['geometry']['coordinates'][1],
            ]);
            
            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info("Seeding selesai!");
    }
}