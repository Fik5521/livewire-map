<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Location;
use Livewire\Attributes\On;

class MapLocation extends Component
{
    public $locationId, $name, $address, $fcode, $lat, $lng;
    public $isEdit = false;
    public $search = '';
    public $kecamatan;
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

    public function save()
    {
        $this->validate([
            'name' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ]);

        \App\Models\Location::create([
            'name' => $this->name . ' (' . ($this->kecamatan ?? 'Luar Wilayah') . ')', // Tambahkan nama kecamatan ke nama industri
            'lat' => $this->lat,
            'lng' => $this->lng,
            // Jika Anda menambah kolom 'kecamatan' di database, simpan di sana
        ]);

        $this->reset(['name', 'lat', 'lng', 'kecamatan']);
        $this->dispatch('refreshMap');
    }
    public function edit($id)
    {
        $loc = Location::findOrFail($id);
        $this->locationId = $id;
        $this->name = $loc->name;
        $this->address = $loc->address;
        $this->fcode = $loc->fcode;
        $this->lat = $loc->lat;
        $this->lng = $loc->lng;
        $this->isEdit = true;

        // Geser peta ke lokasi yang diedit
        $this->dispatch('flyToLocation', lat: $loc->lat, lng: $loc->lng);
    }

    public function update()
    {
        $this->validate();
        $loc = Location::findOrFail($this->locationId);
        $loc->update([
            'name' => $this->name,
            'address' => $this->address,
            'fcode' => $this->fcode,
            'lat' => $this->lat,
            'lng' => $this->lng,
        ]);

        $this->resetInput();
        $this->dispatch('refreshMap');
        session()->flash('message', 'Data Berhasil Diperbarui!');
    }

    public function delete($id)
    {
        Location::destroy($id);
        $this->dispatch('refreshMap');
    }

    public function resetInput()
    {
        $this->reset(['name', 'address', 'fcode', 'lat', 'lng', 'isEdit', 'locationId']);
    }

    // Tidak ada perubahan signifikan di Backend, cukup pastikan data ter-render
    public function render()
    {
        $locations = Location::where('name', 'like', '%' . $this->search . '%')->get();
        return view('livewire.map-location', [
            'dbLocations' => $locations,
            'locations' => $locations,
        ]);
    }
}
