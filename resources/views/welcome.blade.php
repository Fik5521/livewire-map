<!DOCTYPE html>
<html>

<head>
    <title>Laravel Livewire Map CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @livewireStyles
</head>

<body>

    {{ $slot }}

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @livewireScripts
</body>

</html>