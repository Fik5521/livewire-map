<div id="data-industri" class="bg-white min-vh-100 p-4">
    <div class="d-flex justify-content-between align-items-end pb-4 mb-4 border-bottom border-light-subtle">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item small"><a href="#"
                            class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item small active text-primary" aria-current="page">Industri</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-dark mb-0">Database Industri</h2>
            <p class="text-secondary mb-0">Sistem Informasi Geografis Pemetaan Pabrik Kabupaten Bandung</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="openModal" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm">
                <i class="bi bi-plus-lg me-2"></i>Tambah Industri
            </button>
            <button wire:click="exportCsv"
                class="btn btn-white border px-4 py-2 rounded-3 fw-semibold text-dark shadow-sm">
                <i class="bi bi-download me-2"></i>Export
            </button>
            <a href="{{ route('user.dashboard') }}"
                class="btn btn-outline-success px-4 py-2 rounded-3 fw-semibold shadow-sm">
                <i class="bi bi-map me-2"></i>Tampilan User
            </a>
        </div>
    </div>

    <div class="card border border-light-subtle rounded-4 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light-subtle">
            <div class="row align-items-center">
                <div class="col">
                    <div class="d-flex align-items-center">
                        <span class="text-muted small fw-bold text-uppercase me-2">Show</span>
                        <select class="form-select form-select-sm border-light-subtle" style="width: 75px;"
                            wire:model.live="perPage">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-light-subtle">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 border-light-subtle ps-0"
                            placeholder="Cari nama pabrik, kecamatan..." wire:model.live="search">
                    </div>
                </div>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light-subtle border-bottom">
                    <tr>
                        <th class="ps-4 py-3 text-muted fw-semibold small text-uppercase" style="cursor: pointer;"
                            wire:click="sortBy('name')">
                            Nama Industri <i
                                class="bi {{ $sortField === 'name' ? ($sortDirection === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }} ms-1"></i>
                        </th>
                        <th class="py-3 text-muted fw-semibold small text-uppercase" style="cursor: pointer;"
                            wire:click="sortBy('kecamatan')">
                            Kecamatan <i
                                class="bi {{ $sortField === 'kecamatan' ? ($sortDirection === 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }} ms-1"></i>
                        </th>
                        <th class="py-3 text-muted fw-semibold small text-uppercase">Detail Alamat</th>
                        <th class="py-3 text-muted fw-semibold small text-uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($locations as $loc)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">{{ $loc->name }}</div>
                                <small class="text-muted text-uppercase"
                                    style="font-size: 10px;">{{ $loc->fcode }}</small>
                            </td>
                            <td class="py-3">
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-medium">
                                    {{ $loc->kecamatan ?? 'Luar Wilayah' }}
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="text-secondary small w-75">{{ Str::limit($loc->address, 65) }}</div>
                            </td>
                            <td class="pe-4 py-3 text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button wire:click="edit({{ $loc->id }})"
                                        class="btn btn-sm btn-outline-warning" title="Edit" data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <button onclick="confirmDelete({{ $loc->id }})"
                                        class="btn btn-sm btn-outline-danger" title="Hapus" data-bs-toggle="tooltip">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <img src="https://illustrations.popsy.co/white/data-analysis.svg" alt="No data"
                                    style="width: 150px;" class="mb-3">
                                <p class="text-muted">Tidak ada data industri ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3 border-top border-light-subtle">
            <div class="row align-items-center">
                <div class="col-sm-6 text-center text-sm-start mb-3 mb-sm-0">
                    <p class="text-muted small mb-0">
                        Menampilkan <span class="fw-semibold text-dark">{{ $locations->firstItem() ?? 0 }}</span>
                        sampai <span class="fw-semibold text-dark">{{ $locations->lastItem() ?? 0 }}</span>
                        dari <span class="fw-semibold text-dark">{{ $locations->total() }}</span> entri industri
                    </p>
                </div>

                <div class="col-sm-6">
                    <div class="d-flex justify-content-center justify-content-sm-end custom-pagination">
                        {{ $locations->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade @if ($showModal) show @endif"
        style="display: @if ($showModal) block @else none @endif; background: rgba(255,255,255,0.85); backdrop-filter: blur(4px);"
        tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border border-light-subtle shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header px-4 py-3 bg-white border-bottom border-light-subtle">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">
                            {{ $isEdit ? 'Sunting Data Industri' : 'Tambah Industri Baru' }}</h5>
                        <small class="text-muted">Lengkapi detail informasi lokasi industri Anda</small>
                    </div>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">NAMA PERUSAHAAN</label>
                                <input type="text" class="form-control border-light-subtle" wire:model="name"
                                    placeholder="Masukkan nama resmi...">
                                @error('name')
                                    <small class="text-danger mt-1 d-block">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">KECAMATAN TERDETEKSI</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle"><i
                                            class="bi bi-geo"></i></span>
                                    <input type="text" class="form-control bg-light border-light-subtle fw-bold"
                                        wire:model="kecamatan" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">ALAMAT LENGKAP</label>
                                <textarea class="form-control border-light-subtle" wire:model="address" rows="3" placeholder="Jl. Raya No..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">
                                    UPLOAD FOTO (Bisa lebih dari 1)
                                </label>

                                <input type="file" class="form-control border-light-subtle"
                                    wire:model="photo_urls" multiple accept="image/*">

                                @if ($isEdit && !empty($existingPhotos) && count($existingPhotos))
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary">
                                            FOTO TERSIMPAN
                                        </label>

                                        <div class="row g-2">
                                            @foreach ($existingPhotos as $photo)
                                                <div class="col-4">
                                                    <img src="{{ asset('storage/' . $photo->photo_url) }}"
                                                        class="img-fluid rounded border"
                                                        style="height:120px; width:100%; object-fit:cover;">
                                                </div>
                                            @endforeach
                                        </div>

                                        <small class="text-muted d-block mt-2">
                                            *Upload foto baru jika ingin mengganti foto lama
                                        </small>
                                    </div>
                                @endif

                                @error('photo_urls.*')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror

                                {{-- LOADING --}}
                                <div wire:loading wire:target="photo_urls" class="small text-muted mt-2">
                                    <i class="bi bi-arrow-repeat"></i> Mengunggah foto...
                                </div>

                                {{-- PREVIEW MULTI IMAGE --}}
                                @if ($photo_urls)
                                    <div class="mt-3">
                                        <p class="small text-muted mb-2">Preview Foto</p>

                                        <div class="row g-2">
                                            @foreach ($photo_urls as $photo)
                                                <div class="col-4">
                                                    <div class="border rounded-3 p-1 bg-light h-100 text-center">
                                                        <img src="{{ $photo->temporaryUrl() }}"
                                                            class="img-fluid rounded"
                                                            style="height: 120px; width: 100%; object-fit: cover;">
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="p-3 bg-light rounded-3 border border-light-subtle">
                                <label class="form-label small fw-bold text-secondary d-block mb-2">KOORDINAT
                                    SPASIAL</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="text-muted" style="font-size: 10px;">LATITUDE</label>
                                        <input type="text"
                                            class="form-control form-control-sm bg-white border-light-subtle"
                                            wire:model="lat" readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted" style="font-size: 10px;">LONGITUDE</label>
                                        <input type="text"
                                            class="form-control form-control-sm bg-white border-light-subtle"
                                            wire:model="lng" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold text-secondary mb-0">PENENTUAN LOKASI
                                    (INTERAKTIF)</label>
                                <span
                                    class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Klik
                                    pada peta</span>
                            </div>
                            <div id="map-modal" style="height: 420px; border-radius: 12px; border: 1px solid #dee2e6;"
                                wire:ignore></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 bg-light-subtle border-top border-light-subtle">
                    <button type="button" class="btn btn-link text-decoration-none text-muted fw-semibold me-auto"
                        wire:click="closeModal">Batalkan</button>
                    <button type="button" wire:click="{{ $isEdit ? 'update' : 'save' }}"
                        class="btn btn-primary px-5 py-2 rounded-3 fw-bold">
                        {{ $isEdit ? 'Perbarui Data' : 'Simpan Industri' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let modalMap, marker, geoJsonData;

        // Load data kecamatan sekali saat halaman siap
        fetch('/data/32.04_kecamatan.geojson')
            .then(res => res.json())
            .then(data => {
                geoJsonData = data;
            });

        document.addEventListener('livewire:initialized', () => {

            // Listener Inisialisasi Peta dalam Modal
            Livewire.on('initModalMap', (event) => {
                const data = event[0] || null;
                setTimeout(() => {
                    if (modalMap) modalMap.remove();

                    const center = data ? [data.lat, data.lng] : [-7.025, 107.52];
                    const zoom = data ? 16 : 11;

                    modalMap = L.map('map-modal').setView(center, zoom);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(
                        modalMap);

                    if (geoJsonData) {
                        L.geoJSON(geoJsonData, {
                            style: {
                                color: '#4A90E2',
                                weight: 1.5,
                                fillOpacity: 0.1
                            }
                        }).addTo(modalMap);
                    }

                    if (data) marker = L.marker(center).addTo(modalMap);

                    modalMap.on('click', (e) => {
                        if (marker) modalMap.removeLayer(marker);
                        marker = L.marker(e.latlng).addTo(modalMap);

                        let detectedKec = "Luar Wilayah";
                        const pt = turf.point([e.latlng.lng, e.latlng.lat]);
                        if (geoJsonData) {
                            turf.featureEach(geoJsonData, (f) => {
                                if (turf.booleanPointInPolygon(pt, f)) detectedKec =
                                    f.properties.nm_kecamatan;
                            });
                        }
                        @this.dispatch('setCoordinates', {
                            lat: e.latlng.lat,
                            lng: e.latlng.lng,
                            kecamatan: detectedKec
                        });
                    });
                }, 300);
            });

            // SweetAlert Notifications
            Livewire.on('swal:success', (event) => {
                Swal.fire({
                    title: event.title,
                    text: event.text,
                    icon: 'success'
                });
            });

            window.confirmDelete = (id) => {
                Swal.fire({
                    title: 'Yakin hapus data?',
                    text: "Data tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) @this.call('delete', id);
                });
            }
        });
    </script>
</div>
