<div class="container-fluid py-3">
    <div class="row">
        <div class="col-md-3">
            <aside class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <div class="fw-bold">KONTROL & FORM</div>
                    <small class="text-muted">Kelola Lokasi Industri</small>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="switchKec" onchange="toggleKecamatan()" checked>
                                <label class="form-check-label small" for="switchKec"><span id="btnKecText">Sembunyikan Kecamatan</span></label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="switchInd" onchange="toggleIndustri()" checked>
                                <label class="form-check-label small" for="switchInd"><span id="btnIndText">Sembunyikan Industri</span></label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="switchHeat" onchange="toggleHeatmap()">
                                <label class="form-check-label small" for="switchHeat"><span id="btnHeatText">Aktifkan Heatmap</span></label>
                            </div>
                        </div>
                    </div>

                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'save' }}">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Nama Industri</label>
                            <input type="text" class="form-control form-control-sm" wire:model="name" placeholder="Contoh: Pabrik XYZ">
                            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-primary">Kecamatan Terdeteksi</label>
                            <input type="text" class="form-control form-control-sm bg-info-subtle fw-bold" wire:model="kecamatan" readonly placeholder="Klik peta untuk deteksi...">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Latitude</label>
                                <input type="text" class="form-control form-control-sm bg-light" wire:model="lat" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Longitude</label>
                                <input type="text" class="form-control form-control-sm bg-light" wire:model="lng" readonly>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm fw-bold">{{ $isEdit ? 'Perbarui' : 'Simpan' }}</button>
                            <button type="button" wire:click="resetInput" class="btn btn-light btn-sm">Batal</button>
                        </div>
                    </form>
                </div>
            </aside>

            <div class="card shadow-sm" style="max-height: 40vh; overflow-y: auto;">
                <div class="card-body p-2">
                    <input type="text" class="form-control form-control-sm mb-2" placeholder="Cari industri..." wire:model.live="search">
                    <div class="list-group list-group-flush">
                        @foreach(($dbLocations ?? $locations ?? []) as $loc)
                        <div class="list-group-item list-group-item-action py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ $loc->name }}</div>
                                    <?php
                                        $kecName = $loc->kecamatan ?? null;
                                        if (empty($kecName) && !empty($loc->name)) {
                                            if (preg_match('/\(([^)]+)\)$/', $loc->name, $m)) {
                                                $kecName = $m[1];
                                            }
                                        }
                                    ?>
                                    <span class="badge bg-primary" style="font-size: 0.7rem;">Kec. {{ $kecName ?? 'Tidak Diketahui' }}</span>
                                </div>
                                <button wire:click="edit({{ $loc->id }})" class="btn btn-sm btn-outline-secondary">Edit</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div id="map" style="height: 88vh; border-radius: 12px; border: 2px solid #ddd;" wire:ignore></div>
        </div>
    </div>

    <script>
        let map, kecLayer, indLayer, heatLayer;
        let isHeatmapActive = false;

        document.addEventListener('livewire:initialized', () => {
            map = L.map('map').setView([-7.025, 107.52], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // 1. Inisialisasi Layer Kecamatan
            kecLayer = L.geoJSON(null, {
                style: {
                    color: '#4A90E2',
                    weight: 1.5,
                    fillOpacity: 0.05
                }
            }).addTo(map);

            fetch('/data/32.04_kecamatan.geojson')
                .then(res => res.json())
                .then(data => {
                    kecLayer.addData(data);
                    geoJsonData = data; // simpan untuk pengecekan Turf
                });

            // 2. Inisialisasi Layer Industri (Circle)
            indLayer = L.layerGroup().addTo(map);
            const locations = @json($dbLocations ?? $locations ?? []);

            // Siapkan data untuk Heatmap
            let heatData = [];

            // Icon pin kustom (SVG) untuk marker lokasi
            const pinIcon = L.divIcon({
                className: 'custom-pin',
                html: `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 38" width="28" height="44">
                        <path fill="#ef4444" d="M12 0C7.031 0 3 4.031 3 9c0 7.5 9 21 9 21s9-13.5 9-21c0-4.969-4.031-9-9-9zm0 13.5a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9z"/>
                    </svg>
                `,
                iconSize: [28, 44],
                iconAnchor: [14, 42],
                popupAnchor: [0, -40]
            });

            locations.forEach(loc => {
                // Tambahkan marker icon ke layer titik
                L.marker([loc.lat, loc.lng], {
                        icon: pinIcon
                    })
                    .addTo(indLayer)
                    .bindPopup(loc.name);

                // Tambahkan ke array heatmap [lat, lng, intensity]
                heatData.push([loc.lat, loc.lng, 0.5]);
            });

            // 3. Inisialisasi Heatmap (tapi jangan tampilkan dulu)
            heatLayer = L.heatLayer(heatData, {
                radius: 25,
                blur: 15,
                maxZoom: 17,
                gradient: {
                    0.4: 'blue',
                    0.65: 'lime',
                    1: 'red'
                }
            });

            // Set initial state for switches (UI)
            try {
                const switchKec = document.getElementById('switchKec');
                const switchInd = document.getElementById('switchInd');
                const switchHeat = document.getElementById('switchHeat');

                if (switchKec) {
                    switchKec.checked = map.hasLayer(kecLayer);
                    document.getElementById('btnKecText').innerText = map.hasLayer(kecLayer) ? 'Sembunyikan Kecamatan' : 'Tampilkan Kecamatan';
                }
                if (switchInd) {
                    switchInd.checked = map.hasLayer(indLayer);
                    document.getElementById('btnIndText').innerText = map.hasLayer(indLayer) ? 'Sembunyikan Industri' : 'Tampilkan Industri';
                }
                if (switchHeat) {
                    switchHeat.checked = isHeatmapActive;
                    document.getElementById('btnHeatText').innerText = isHeatmapActive ? 'Matikan Heatmap' : 'Aktifkan Heatmap';
                }
            } catch (e) {
                // ignore UI init errors
            }

            // --- FUNGSI TOMBOL ---

            window.toggleKecamatan = function() {
                if (map.hasLayer(kecLayer)) {
                    map.removeLayer(kecLayer);
                    document.getElementById('btnKecText').innerText = "Tampilkan Garis Kecamatan";
                } else {
                    kecLayer.addTo(map);
                    document.getElementById('btnKecText').innerText = "Hilangkan Garis Kecamatan";
                }
            }

            window.toggleIndustri = function() {
                if (map.hasLayer(indLayer)) {
                    map.removeLayer(indLayer);
                    document.getElementById('btnIndText').innerText = "Tampilkan Titik Industri";
                } else {
                    indLayer.addTo(map);
                    document.getElementById('btnIndText').innerText = "Hilangkan Titik Industri";
                }
            }

            window.toggleHeatmap = function() {
                if (!isHeatmapActive) {
                    heatLayer.addTo(map);
                    map.removeLayer(indLayer); // Biasanya heatmap lebih bagus tanpa marker titik
                    document.getElementById('btnHeatText').innerText = "Matikan Heatmap";
                    isHeatmapActive = true;
                } else {
                    map.removeLayer(heatLayer);
                    indLayer.addTo(map);
                    document.getElementById('btnHeatText').innerText = "Aktifkan Heatmap";
                    isHeatmapActive = false;
                }
            }

            // Click Map & Listeners (dengan deteksi kecamatan menggunakan Turf)
            map.on('click', e => {
                const pt = turf.point([e.latlng.lng, e.latlng.lat]);
                let detectedKec = "Tidak Terdeteksi";

                if (geoJsonData) {
                    turf.featureEach(geoJsonData, (currentFeature) => {
                        if (turf.booleanPointInPolygon(pt, currentFeature)) {
                            detectedKec = currentFeature.properties.nm_kecamatan || detectedKec;
                        }
                    });
                }

                // Kirim koordinat DAN nama kecamatan ke Livewire
                @this.dispatch('setCoordinates', {
                    lat: e.latlng.lat,
                    lng: e.latlng.lng,
                    kecamatan: detectedKec
                });

                // Tampilkan popup sementara di lokasi klik
                L.popup()
                    .setLatLng(e.latlng)
                    .setContent("Lokasi dipilih di Kec. " + detectedKec)
                    .openOn(map);
            });

            Livewire.on('flyToLocation', d => {
                map.flyTo([d.lat, d.lng], 16);
            });
        });
        let geoJsonData; // Variabel global untuk menyimpan data GeoJSON

        fetch('/data/32.04_kecamatan.geojson')
            .then(res => res.json())
            .then(data => {
                geoJsonData = data; // Simpan data untuk pengecekan Turf
                kecLayer.addData(data);
            });

        // Modifikasi Event Click Map
        map.on('click', e => {
            const pt = turf.point([e.latlng.lng, e.latlng.lat]);
            let detectedKec = "Tidak Terdeteksi";

            if (geoJsonData) {
                // Lakukan pengecekan Point-in-Polygon
                turf.featureEach(geoJsonData, (currentFeature) => {
                    if (turf.booleanPointInPolygon(pt, currentFeature)) {
                        detectedKec = currentFeature.properties.nm_kecamatan;
                    }
                });
            }

            // Kirim koordinat DAN nama kecamatan ke Livewire
            @this.dispatch('setCoordinates', {
                lat: e.latlng.lat,
                lng: e.latlng.lng,
                kecamatan: detectedKec
            });

            // Tampilkan popup sementara di lokasi klik
            L.popup()
                .setLatLng(e.latlng)
                .setContent("Lokasi dipilih di Kec. " + detectedKec)
                .openOn(map);
        });
    </script>
</div>