<?php

namespace App\Livewire;

use App\Models\LocationDetail;
use Livewire\Component;
use App\Models\Location;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
class MapLocation extends Component
{

    use WithPagination; //Aktifkan pagination
    use WithFileUploads;

    public $locationId, $name, $kecamatan, $address, $fcode, $radius = 150, $lat, $lng;
    public $isEdit = false;
    public $showModal = false;
    public $search = '';
    //Properti untuk kontrol tabel
    public $perPage = 5; //Default 5 data per halaman
    public $sortField = 'created_at'; //Urutan default (terbaru)
    public $sortDirection = 'desc';
    public $photo_urls = [];
    public $existingPhotos = [];

    //Reset halaman ke nomor 1 setiap kali user mengetik di kolom pencarian
    public function updatingSearch()
    {
        $this->resetPage();
    }

    //Reset halaman ke nomor 1 setiap kali user mengubah perPage
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
        //Mendukung payload berbentuk object/array (dari JS dispatch) atau arg positional
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
        $this->dispatch('initModalMap'); //Memicu inisialisasi peta saat modal terbuka
    }

    public function closeModal()
    {
        $this->showModal = false;
    }
    public function save()
    {
        $this->validate([
            'name' => 'required',
            'lat' => 'required',
            'photo_urls' => 'required|array',
            'photo_urls.*' => 'image|max:2048',
        ]);

        // $photoPath = [];

        $Location = Location::create([
            'name' => $this->name,
            'kecamatan' => $this->kecamatan,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
        ]);

        foreach ($this->photo_urls as $photo) {
            $path = $photo->store('locations', 'public');

            LocationDetail::create([
                'location_id' => $Location->id,
                'photo_url' => $path,
            ]);
        }
        $this->reset();
        $this->closeModal();

        $this->dispatch(
            'swal:success',
            title: 'Berhasil!',
            text: 'Data industri baru telah ditambahkan.'
        );
    }

    public function edit($id)
    {
        $loc = Location::with('details')->findOrFail($id);

        $this->locationId = $id;
        $this->name = $loc->name;
        $this->kecamatan = $loc->kecamatan;
        $this->address = $loc->address;
        $this->lat = $loc->lat;
        $this->lng = $loc->lng;

        // simpan foto lama untuk preview
        $this->existingPhotos = $loc->details ?? [];
        ;

        $this->photo_urls = []; //jangan isi dengan foto lama

        $this->isEdit = true;
        $this->showModal = true;

        $this->dispatch('initModalMap', [
            'lat' => $loc->lat,
            'lng' => $loc->lng,
            'name' => $loc->name
        ]);
    }

    public function update()
    {
        $this->validate([
            'name' => 'required',
            'lat' => 'required',
            'photo_urls.*' => 'image|max:2048',
        ]);

        $loc = Location::with('details')->findOrFail($this->locationId);

        // update data utama
        $loc->update([
            'name' => $this->name,
            'kecamatan' => $this->kecamatan,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
        ]);

        //JIKA ADA FOTO BARU
        if ($this->photo_urls && count($this->photo_urls) > 0) {

            //hapus foto lama (file + DB)
            foreach ($loc->details as $detail) {
                if (Storage::disk('public')->exists($detail->photo_url)) {
                    Storage::disk('public')->delete($detail->photo_url);
                }
                $detail->delete();
            }

            //simpan foto baru
            foreach ($this->photo_urls as $photo) {
                $path = $photo->store('locations', 'public');

                LocationDetail::create([
                    'location_id' => $loc->id,
                    'photo_url' => $path,
                ]);
            }
        }

        $this->reset(['photo_urls', 'existingPhotos']);
        $this->closeModal();

        $this->dispatch(
            'swal:success',
            title: 'Diperbarui!',
            text: 'Data industri berhasil diubah.'
        );
    }

    public function delete($id)
    {
        try {
            $loc = Location::with('details')->findOrFail($id);

            // 1️⃣ HAPUS FILE FOTO DI STORAGE
            foreach ($loc->details as $detail) {
                if ($detail->photo_url && Storage::disk('public')->exists($detail->photo_url)) {
                    Storage::disk('public')->delete($detail->photo_url);
                }
            }

            // 2️⃣ HAPUS DATA DETAIL FOTO (DB)
            $loc->details()->delete();

            // 3️⃣ HAPUS DATA LOCATION
            $loc->delete();

            $this->dispatch(
                'swal:success',
                title: 'Dihapus!',
                text: 'Data industri dan seluruh fotonya berhasil dihapus.'
            );

        } catch (\Exception $e) {
            $this->dispatch(
                'swal:error',
                title: 'Gagal!',
                text: 'Data tidak bisa dihapus.'
            );
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
