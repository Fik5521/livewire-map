<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Location;
use Illuminate\Support\Facades\File;

class UpdateLocationKecamatan extends Command
{
    protected $signature = 'locations:update-kecamatan';
    protected $description = 'Update nama kecamatan untuk 150+ data industri berdasarkan GeoJSON';

    public function handle()
    {
        $path = public_path('data/32.04_kecamatan.geojson');

        if (!File::exists($path)) {
            $this->error("File GeoJSON tidak ditemukan di: " . $path);
            return;
        }

        $geoJson = json_decode(File::get($path), true);
        $locations = Location::all();

        $this->info("Memproses " . $locations->count() . " data industri...");
        $bar = $this->output->createProgressBar($locations->count());

        foreach ($locations as $location) {
            $detectedKec = "Luar Wilayah";

            foreach ($geoJson['features'] as $feature) {
                $polygon = $feature['geometry']['coordinates'];
                $kecName = $feature['properties']['nm_kecamatan'];

                // Karena data Anda 'MultiPolygon', kita ambil array pertama
                if ($feature['geometry']['type'] === 'MultiPolygon') {
                    foreach ($polygon as $poly) {
                        if ($this->isPointInPolygon($location->lng, $location->lat, $poly[0])) {
                            $detectedKec = $kecName;
                            break 2;
                        }
                    }
                } else {
                    if ($this->isPointInPolygon($location->lng, $location->lat, $polygon[0])) {
                        $detectedKec = $kecName;
                        break;
                    }
                }
            }

            // Update database: Menggabungkan nama industri dengan kecamatan
            // Contoh: "PT. ABC" menjadi "PT. ABC (Cileunyi)"
            $location->update([
                'name' => $location->name . " (" . $detectedKec . ")",
                // Jika Anda punya kolom 'kecamatan' sendiri, isi di sini:
                // 'kecamatan' => $detectedKec 
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nBerhasil memperbarui semua data!");
    }

    /**
     * Algoritma Ray Casting untuk mendeteksi apakah titik berada di dalam poligon
     */
    private function isPointInPolygon($longitude, $latitude, $vertices)
    {
        $intersect = 0;
        $n = count($vertices);

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;

            $vi = $vertices[$i];
            $vj = $vertices[$j];

            if (
                $vi[1] == $vj[1] && $vi[1] == $latitude &&
                $longitude > min($vi[0], $vj[0]) && $longitude < max($vi[0], $vj[0])
            ) {
                return true;
            }

            if (
                $latitude > min($vi[1], $vj[1]) && $latitude <= max($vi[1], $vj[1]) &&
                $longitude <= max($vi[0], $vj[0]) && $vi[1] != $vj[1]
            ) {
                $xinters = ($latitude - $vi[1]) * ($vj[0] - $vi[0]) / ($vj[1] - $vi[1]) + $vi[0];
                if ($vi[0] == $vj[0] || $longitude <= $xinters) {
                    $intersect++;
                }
            }
        }

        return ($intersect % 2 != 0);
    }
}
