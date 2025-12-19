<div class="border rounded-lg p-4">
    <h3 class="text-xl font-bold mb-4">Karte der Nullmessungen</h3>
    
    @if($project->nullMeasurements->count() > 0)
        <div id="map" class="w-full h-[600px] rounded-lg border"></div>
        
        <div class="mt-4 text-sm text-gray-600">
            <p><strong>Anzahl Punkte:</strong> {{ $project->nullMeasurements->count() }}</p>
            <p class="mt-2"><strong>Hinweis:</strong> Die Koordinaten werden von LV95 (Swiss coordinates) zu WGS84 (GPS) konvertiert.</p>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-yellow-800">Keine Nullmessungen vorhanden. Bitte importieren Sie zuerst Nullmessungen im Import-Tab.</p>
        </div>
    @endif
</div>

@if($project->nullMeasurements->count() > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration constants
    const SWITZERLAND_CENTER_LAT = 46.8182;
    const SWITZERLAND_CENTER_LNG = 8.2275;
    const DEFAULT_ZOOM = 8;
    const MAP_INIT_DELAY = 100; // Delay in milliseconds to ensure container is visible
    
    // Initialize the map only when the map tab is shown
    let mapInitialized = false;
    let map = null;

    function initializeMap() {
        if (mapInitialized) return;
        
        // Create the map centered on Switzerland
        map = L.map('map').setView([SWITZERLAND_CENTER_LAT, SWITZERLAND_CENTER_LNG], DEFAULT_ZOOM);

        // Add OpenStreetMap tiles
        L.tileLayer('https://wmts.geo.admin.ch/1.0.0/ch.swisstopo.swissimage/default/current/3857/{z}/{x}/{y}.jpeg', {
            attribution: '<a href="https://www.geo.admin.ch/en/general-terms-of-use-fsdi">geo.admin.ch</a>',
            maxZoom: 19
        }).addTo(map);

        // Fetch and display the measurement points
        fetch('{{ route('projects.map-data', $project) }}')
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) return;

                const bounds = [];
                
                data.forEach(measurement => {
                    const marker = L.marker([measurement.lat, measurement.lng]).addTo(map);
                    
                    // Create popup with measurement details
                    const popupContent = `
                        <div class="p-2">
                            <h4 class="font-bold text-lg mb-2">${measurement.punkt}</h4>
                            <table class="text-sm">
                                <tr><td class="pr-2"><strong>E:</strong></td><td>${parseFloat(measurement.E).toFixed(3)}</td></tr>
                                <tr><td class="pr-2"><strong>N:</strong></td><td>${parseFloat(measurement.N).toFixed(3)}</td></tr>
                                <tr><td class="pr-2"><strong>H:</strong></td><td>${parseFloat(measurement.H).toFixed(3)}</td></tr>
                                <tr><td class="pr-2"><strong>Datum:</strong></td><td>${measurement.date}</td></tr>
                            </table>
                        </div>
                    `;
                    
                    marker.bindPopup(popupContent);
                    bounds.push([measurement.lat, measurement.lng]);
                });

                // Fit map to show all markers
                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [50, 50] });
                }
            })
            .catch(error => {
                console.error('Error loading map data:', error);
            });

        mapInitialized = true;
    }

    // Check if map tab is active on page load
    const mapTab = document.getElementById('tab-map');
    if (mapTab) {
        // Listen for tab changes
        const originalShowTab = window.showTab;
        window.showTab = function(tabName) {
            originalShowTab(tabName);
            if (tabName === 'map') {
                // Delay to ensure the map container is visible
                setTimeout(initializeMap, MAP_INIT_DELAY);
            }
        };
    }
});
</script>
@endif
