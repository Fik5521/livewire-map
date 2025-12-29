<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GIS Industri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

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
        }

        .main-content {
            margin-left: 240px;
            padding: 20px;
        }

        #map-modal {
            height: 350px;
            width: 100%;
            border-radius: 8px;
        }
    </style>
    @livewireStyles
</head>

<body>
    <div class="sidebar text-white">
        <div class="px-3 mb-4">
            <h4>GIS ADMIN</h4>
            <hr>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link text-white active" href="#">📊 Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#">🏢 Data Industri</a></li>
        </ul>
    </div>

    <main class="main-content">
        {{ $slot }}
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    @livewireScripts
    @stack('scripts')
</body>

</html>