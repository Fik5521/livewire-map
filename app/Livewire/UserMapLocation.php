<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Location;
use Livewire\Attributes\On;

class UserMapLocation extends Component
{
    public $search = '';
    public $selectedLocation = null;
    public $triggerMapUpdate = true;

    public function render()
    {
        $locations = Location::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('kecamatan', 'like', '%' . $this->search . '%')
            ->get();

        if ($this->triggerMapUpdate) {
            $this->dispatch(
                event: 'updateUserMap',
                locations: $locations
            );
        }

        // reset flag setelah dispatch
        $this->triggerMapUpdate = false;

        return view('livewire.user-map-location', [
            'locations' => $locations
        ])->layout('layouts.user');
    }

    public function updatedSearch()
    {
        //hanya search yg boleh update map
        $this->triggerMapUpdate = true;
    }

    #[On('showLocationDetail')]
    public function showLocationDetail($id)
    {
        $this->selectedLocation = Location::with('details')->find($id);

        //jangan update map pas marker diklik
        $this->triggerMapUpdate = false;

        logger('DETAILS:', $this->selectedLocation->details->toArray());
    }

    #[On('closeLocationDetail')]
    public function closeLocationDetail()
    {
        $this->selectedLocation = null;
    }

}
