<x-app-layout>
    <x-slot name="title">Peta Sebaran Alumni SISTA</x-slot>

    <!-- Leaflet.js CSS -->
    <x-slot name="styles">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            .leaflet-container {
                font-family: inherit;
            }
        </style>
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Peta Geografis Sebaran Alumni
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Visualisasi lokasi domisili alumni SMA Islam Sultan Agung 1 Semarang secara geografis.
                </p>
            </div>
            <a href="{{ route('alumni.directory') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Direktori Alumni
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Container Peta -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div id="map" class="w-full h-[550px] rounded-2xl shadow-inner z-0"></div>
        </div>

        <!-- Ringkasan Lokasi Sebaran -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm"> Wilayah Persebaran Terbanyak</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                @foreach($locations->sortByDesc('total')->take(4) as $loc)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 text-center">
                        <span class="block text-[10px] text-gray-400 font-bold uppercase">{{ $loc['province'] ?? 'Domisili' }}</span>
                        <h4 class="font-black text-gray-800 dark:text-white text-sm mt-1">{{ $loc['city'] }}</h4>
                        <p class="text-blue-600 dark:text-blue-400 font-extrabold text-xs mt-1">{{ $loc['total'] }} Alumni</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Leaflet.js Script -->
    <x-slot name="scripts">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Initialize map centered at Indonesia
                var map = L.map('map', {
                    scrollWheelZoom: false
                }).setView([-2.548926, 118.014863], 5);

                // Add standard OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Locations data from Laravel
                var locations = @json($locations);

                locations.forEach(function(loc) {
                    if (loc.lat && loc.lng) {
                        // Create marker
                        var marker = L.marker([loc.lat, loc.lng]).addTo(map);
                        
                        // Create popup content
                        var popupContent = `
                            <div class="p-2 space-y-1">
                                <h4 class="font-black text-xs text-gray-800 m-0">${loc.city}, ${loc.province || ''}</h4>
                                <p class="text-[10px] text-gray-500 m-0">${loc.country}</p>
                                <p class="text-blue-600 font-bold text-xs m-0 mt-1"> ${loc.total} Alumni Berdomisili</p>
                            </div>
                        `;
                        marker.bindPopup(popupContent);
                    }
                });
            });
        </script>
    </x-slot>
</x-app-layout>
