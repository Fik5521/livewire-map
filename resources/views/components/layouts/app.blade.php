<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GIS Industri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            font-size: .875rem;
            background-color: #f8f9fa;
        }

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            width: 240px;
            background: #212529;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .main-content {
            margin-left: 240px;
            padding: 20px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sidebar-logo h4 {
            margin: 0;
            font-size: 16px;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .sidebar-logo h4 {
            display: none;
        }

        .sidebar-toggle-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 8px;
        }

        .sidebar-toggle-btn:hover {
            opacity: 0.8;
        }

        .sidebar-toggle-btn:active {
            opacity: 0.6;
        }

        .sidebar-toggle-btn i {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.collapsed .sidebar-toggle-btn i {
            transform: rotate(180deg);
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            font-size: 14px;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 10px;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        #map-modal {
            height: 350px;
            width: 100%;
            border-radius: 8px;
        }

        .map-controls {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 1000;
            display: flex;
            gap: 6px;
        }

        .map-control-btn {
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 13px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.95);
            cursor: pointer;
        }

        .map-control-btn.active {
            background: #0d6efd;
            color: #fff;
            border-color: rgba(13, 110, 253, 0.9);
        }
    </style>
    @livewireStyles
</head>

<body>
    <div class="sidebar text-white" id="sidebar">
        <div class="sidebar-logo px-3 mb-2">
            <h4>GIS ADMIN</h4>
            <button class="sidebar-toggle-btn" id="sidebar-toggle" title="Toggle Sidebar">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white active" href="#dashboard" data-scroll>
                    <i class="bi bi-speedometer2 me-2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="#data-industri" data-scroll>
                    <i class="bi bi-building me-2"></i>
                    <span class="nav-text">Data Industri</span>
                </a>
            </li>
        </ul>
    </div>

    <main class="main-content">
        <section id="dashboard" class="mb-4">
            <div class="d-flex justify-content-between align-items-end pb-4 mb-4 border-bottom border-light-subtle">
                <div>
                    <h2 class="h3 fw-bold text-dark mb-1">Peta Penyebaran Industri</h2>
                    <p class="text-secondary mb-0">Visualisasi geografis dan analisis distribusi pabrik di Kabupaten
                        Bandung</p>
                </div>
                <div class="d-flex gap-2">
                    <div class="text-end">
                        <small class="text-muted text-uppercase d-block">Total Industri</small>
                        <h5 class="fw-bold text-dark mb-0" id="total-locations">--</h5>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body p-0">
                    <div id="main-map" style="height:600px; border-radius: 12px; overflow:hidden; position: relative;">
                        <div class="map-controls" aria-hidden="true">
                            <button id="btn-toggle-heat" class="map-control-btn active">Heatmap</button>
                            <button id="btn-toggle-boundaries" class="map-control-btn active">Batas Kec.</button>
                            <button id="btn-toggle-markers" class="map-control-btn active">Titik</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{ $slot }}
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    @livewireScripts
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            const toggleBtn = document.getElementById('sidebar-toggle');

            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });

            // Smooth scroll for sidebar links
            document.querySelectorAll('[data-scroll]').forEach(el => {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });

            // Initialize main dashboard map and load locations + heatmap
            if (document.getElementById('main-map')) {
                const map = L.map('main-map', {
                    preferCanvas: true
                }).setView([-7.025, 107.52], 11);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                // layers
                const markersLayer = L.layerGroup().addTo(map);
                let heatLayer = null;
                let boundariesLayer = null;

                // button refs
                const btnHeat = document.getElementById('btn-toggle-heat');
                const btnBound = document.getElementById('btn-toggle-boundaries');
                const btnMarks = document.getElementById('btn-toggle-markers');

                // load locations and draw markers + heatmap
                fetch('/locations-data')
                    .then(res => res.json())
                    .then(data => {
                        // update total count
                        document.getElementById('total-locations').textContent = data.length;

                        const heatPoints = [];
                        data.forEach(loc => {
                            const lat = parseFloat(loc.lat);
                            const lng = parseFloat(loc.lng);
                            if (!isNaN(lat) && !isNaN(lng)) {
                                const m = L.marker([lat, lng]).bindPopup(
                                    `<strong>${loc.name}</strong><br>${loc.kecamatan || ''}`);
                                markersLayer.addLayer(m);
                                heatPoints.push([lat, lng, 0.5]);
                            }
                        });

                        if (heatPoints.length) {
                            heatLayer = L.heatLayer(heatPoints, {
                                radius: 30,
                                blur: 20,
                                maxZoom: 17,
                                gradient: {
                                    0.0: '#ffff99',
                                    0.25: '#ff9900',
                                    0.5: '#ff6600',
                                    0.75: '#ff3300',
                                    1.0: '#cc0000'
                                }
                            }).addTo(map);
                        }

                        // fetch kecamatan boundaries and draw them on top
                        fetch('/data/32.04_kecamatan.geojson')
                            .then(r => r.json())
                            .then(geo => {
                                boundariesLayer = L.geoJSON(geo, {
                                    style: {
                                        color: '#4A90E2',
                                        weight: 1.5,
                                        fillOpacity: 0.06
                                    },
                                    onEachFeature: function(feature, layer) {
                                        if (feature.properties && feature.properties
                                            .nm_kecamatan) {
                                            layer.bindTooltip(feature.properties.nm_kecamatan, {
                                                sticky: true
                                            });
                                        }
                                    }
                                }).addTo(map);
                            })
                            .catch(() => {});
                    })
                    .catch(() => {
                        // silently ignore; map will still show base layer
                    });

                // toggle helpers
                function setLayerVisibility(layer, visible, btn) {
                    if (!layer) return;
                    if (visible) {
                        if (!map.hasLayer(layer)) map.addLayer(layer);
                        btn.classList.add('active');
                    } else {
                        if (map.hasLayer(layer)) map.removeLayer(layer);
                        btn.classList.remove('active');
                    }
                }

                btnHeat.addEventListener('click', function() {
                    const visible = !this.classList.contains('active');
                    setLayerVisibility(heatLayer, visible, this);
                });
                btnBound.addEventListener('click', function() {
                    const visible = !this.classList.contains('active');
                    setLayerVisibility(boundariesLayer, visible, this);
                });
                btnMarks.addEventListener('click', function() {
                    const visible = !this.classList.contains('active');
                    setLayerVisibility(markersLayer, visible, this);
                });

                // ensure proper rendering after insertion
                setTimeout(() => {
                    map.invalidateSize();
                }, 300);
            }
        });
    </script>
</body>

</html>
