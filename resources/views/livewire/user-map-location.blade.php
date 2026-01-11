<div class="container-fluid px-4 py-3">

    {{-- HEADER --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-0">Peta Persebaran Industri</h4>
        <small class="text-muted">Kabupaten Bandung</small>

        <div class="mt-3">
            <input type="text" class="form-control form-control-lg rounded-pill shadow-sm"
                placeholder="Cari nama pabrik atau kecamatan..." wire:model.live="search">
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="row g-3">

        {{-- list --}}
        <div class="col-md-3" style="max-height: 70vh; overflow-y: auto;">
            <div class="list-group">
                @forelse($locations as $loc)
                    <button class="list-group-item list-group-item-action"
                        onclick="focusMarker({{ $loc->lat }}, {{ $loc->lng }})">
                        <div class="fw-semibold">{{ $loc->name }}</div>
                        <small class="text-muted">
                            {{ $loc->kecamatan ?? 'Luar Wilayah' }}
                        </small>
                    </button>
                @empty
                    <div class="list-group-item text-muted">
                        Data tidak ditemukan
                    </div>
                @endforelse
            </div>
        </div>


        {{-- MAP --}}
        <div class="col-lg-9 col-md-8 position-relative">

            <div id="map-user" wire:ignore class="rounded-4 shadow-sm" style="height: 70vh;">
            </div>

            {{-- DETAIL CARD (OVERLAY MAP) --}}
            @if ($selectedLocation)
                <div class="card border-0 shadow position-absolute"
                    style="bottom:20px; left:20px; width:340px; z-index:1000;">

                    <div class="card-body">

                        <h6 class="fw-bold mb-1">
                            {{ $selectedLocation->name }}
                        </h6>

                        <small class="text-muted d-block mb-2">
                            Kecamatan {{ $selectedLocation->kecamatan }}
                        </small>

                        <p class="small mb-3">
                            {{ $selectedLocation->address }}
                        </p>

                        {{-- FOTO --}}
                        <div class="row g-2">
                            @forelse($selectedLocation->details as $photo)
                                <div class="col-4">
                                    <img src="{{ Storage::url($photo->photo_url) }}" class="img-fluid rounded"
                                        style="height:80px; object-fit:cover;">
                                </div>
                            @empty
                                <small class="text-muted">
                                    Tidak ada foto
                                </small>
                            @endforelse
                        </div>

                    </div>
                </div>
            @endif
        </div>

    </div>

    {{-- SCRIPT (TIDAK DIUBAH) --}}
    <script>
        let map;
        let markers = [];
        let heatLayer = null;
        let mapInitialized = false;

        document.addEventListener('livewire:initialized', () => {

            if (mapInitialized) return;

            map = L.map('map-user').setView([-7.025, 107.52], 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18
            }).addTo(map);

            heatLayer = L.heatLayer([], {
                radius: 50,
                blur: 18,
                maxZoom: 17
            }).addTo(map);

            fetch('/data/32.04_kecamatan.geojson')
                .then(res => res.json())
                .then(data => {
                    L.geoJSON(data, {
                        style: {
                            color: '#2563eb',
                            weight: 1.5,
                            fillOpacity: 0.05
                        }
                    }).addTo(map);
                });

            mapInitialized = true;

            Livewire.on('updateUserMap', (data) => {

                markers.forEach(m => map.removeLayer(m));
                markers = [];
                heatLayer.setLatLngs([]);

                if (!data.locations.length) return;

                let heatPoints = [];

                data.locations.forEach(loc => {
                    if (!loc.lat || !loc.lng) return;

                    const marker = L.marker([loc.lat, loc.lng]).addTo(map);

                    marker.bindPopup(`<strong>${loc.name}</strong>`);

                    marker.on('click', () => {
                        Livewire.dispatch('showLocationDetail', {
                            id: loc.id
                        });
                    });

                    marker.on('popupclose', () => {
                        Livewire.dispatch('closeLocationDetail');
                    });

                    markers.push(marker);

                    heatPoints.push([loc.lat, loc.lng, 0.6]);
                });

                heatLayer.setLatLngs(heatPoints);

                if (markers.length > 1) {
                    const group = L.featureGroup(markers);
                    map.fitBounds(group.getBounds(), {
                        padding: [50, 50]
                    });
                }
            });
        });

        function focusMarker(lat, lng) {
            if (!map) return;
            map.setView([lat, lng], 15);
        }
    </script>
</div>
