<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Location;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class MapLocation extends Component
{

    use WithPagination; // Aktifkan pagination

    public $locationId, $name, $kecamatan, $address, $fcode, $radius = 150, $lat, $lng;
    public $isEdit = false;
    public $showModal = false;
    public $search = '';

    // Properti untuk kontrol tabel
    public $perPage = 5; // Default 5 data per halaman
    public $sortField = 'created_at'; // Urutan default (terbaru)
    public $sortDirection = 'desc';

    // Reset halaman ke nomor 1 setiap kali user mengetik di kolom pencarian
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Reset halaman ke nomor 1 setiap kali user mengubah perPage
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        return view('livewire.map-location', [
            'locations' => Location::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('kecamatan', 'like', '%' . $this->search . '%')
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage)
                ->withPath('/locations')
                ->appends(request()->query())
        ]);
    }
    protected $rules = [
        'name' => 'required|min:3',
        'lat' => 'required',
        'lng' => 'required',
    ];

    #[On('setCoordinates')]
    public function setCoordinates()
    {
        // Mendukung payload berbentuk object/array (dari JS dispatch) atau arg positional
        $args = func_get_args();
        if (count($args) === 1 && (is_array($args[0]) || is_object($args[0]))) {
            $p = (array) $args[0];
        } else {
            $p = [];
            $p['lat'] = $args[0] ?? null;
            $p['lng'] = $args[1] ?? null;
            $p['kecamatan'] = $args[2] ?? null;
        }

        $this->lat = $p['lat'] ?? $this->lat;
        $this->lng = $p['lng'] ?? $this->lng;
        $this->kecamatan = $p['kecamatan'] ?? $this->kecamatan;
    }

    public function openModal()
    {
        $this->resetInput();
        $this->showModal = true;
        $this->dispatch('initModalMap'); // Memicu inisialisasi peta saat modal terbuka
    }

    public function closeModal()
    {
        $this->showModal = false;
    }
    public function save()
    {
        $this->validate(['name' => 'required', 'lat' => 'required']);

        try {
            \App\Models\Location::create([
                'name' => $this->name,
                'kecamatan' => $this->kecamatan,
                'address' => $this->address,
                'lat' => $this->lat,
                'lng' => $this->lng,
            ]);

            $this->closeModal();
            $this->dispatch('swal:success', title: 'Berhasil!', text: 'Data industri baru telah ditambahkan.');
        } catch (\Exception $e) {
            $this->dispatch('swal:error', title: 'Gagal!', text: 'Terjadi kesalahan saat menyimpan data.');
        }
    }
    public function edit($id)
    {
        $loc = \App\Models\Location::findOrFail($id);
        $this->locationId = $id;
        $this->name = $loc->name;
        $this->kecamatan = $loc->kecamatan;
        $this->address = $loc->address;
        $this->fcode = $loc->fcode;
        $this->radius = $loc->radius;
        $this->lat = $loc->lat;
        $this->lng = $loc->lng;

        $this->isEdit = true;
        $this->showModal = true;

        // Kirim perintah ke JS untuk memindahkan peta ke titik industri yang diedit
        $this->dispatch('initModalMap', [
            'lat' => $loc->lat,
            'lng' => $loc->lng,
            'name' => $loc->name
        ]);
    }

    public function update()
    {
        try {
            $loc = \App\Models\Location::find($this->locationId);
            $loc->update([
                'name' => $this->name,
                'kecamatan' => $this->kecamatan,
                'address' => $this->address,
                'lat' => $this->lat,
                'lng' => $this->lng,
            ]);

            $this->closeModal();
            $this->dispatch('swal:success', title: 'Diperbarui!', text: 'Data industri berhasil diubah.');
        } catch (\Exception $e) {
            $this->dispatch('swal:error', title: 'Gagal!', text: 'Data gagal diperbarui.');
        }
    }

    public function delete($id)
    {
        try {
            \App\Models\Location::destroy($id);
            $this->dispatch('swal:success', title: 'Dihapus!', text: 'Data industri telah dihapus dari sistem.');
        } catch (\Exception $e) {
            $this->dispatch('swal:error', title: 'Gagal!', text: 'Data tidak bisa dihapus.');
        }
    }

    public function exportCsv()
    {
        $fileName = 'locations_' . now()->format('Ymd_His') . '.csv';

        $locations = Location::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('kecamatan', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($locations) {
            $out = fopen('php://output', 'w');
            // BOM for UTF-8 to help Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Name', 'Kecamatan', 'Address', 'Latitude', 'Longitude', 'Fcode', 'Created At']);

            foreach ($locations as $loc) {
                fputcsv($out, [
                    $loc->id,
                    $loc->name,
                    $loc->kecamatan,
                    $loc->address,
                    $loc->lat,
                    $loc->lng,
                    $loc->fcode,
                    optional($loc->created_at)->toDateTimeString(),
                ]);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    public function resetInput()
    {
        $this->reset(['name', 'address', 'fcode', 'lat', 'lng', 'isEdit', 'locationId']);
    }
}
