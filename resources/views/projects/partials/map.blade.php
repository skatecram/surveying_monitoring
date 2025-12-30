<div class="border rounded-lg p-4">
    <h3 class="text-xl font-bold mb-4">Karte der Messungen</h3>
    
    @if($project->nullMeasurements->count() > 0)
        <div id="map" class="w-full h-[600px] rounded-lg border"></div>
        
        <div class="mt-4 space-y-2">
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow"></div>
                    <span><strong>Nullmessungen:</strong> {{ $project->nullMeasurements->count() }} Punkte</span>
                </div>
                @if($project->controlMeasurements->count() > 0)
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full bg-red-500 border-2 border-white shadow"></div>
                    <span><strong>Letzte Kontrollmessungen:</strong> {{ $project->controlMeasurements->groupBy('punkt')->count() }} Punkte</span>
                </div>
                @endif
            </div>
            <p class="text-sm text-gray-600"><strong>Hinweis:</strong> Die Koordinaten werden von LV95 (Swiss coordinates) zu WGS84 (GPS) konvertiert.</p>
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

    function createCustomIcon(color) {
        return L.divIcon({
            className: 'custom-marker',
            html: `<div style="background-color: ${color}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
            iconSize: [12, 12],
            iconAnchor: [6, 6],
            popupAnchor: [0, -6]
        });
    }

    function createPopupContent(measurement, label) {
        return `
            <div class="p-2">
                <h4 class="font-bold text-lg mb-2">${measurement.punkt}</h4>
                <p class="text-xs text-gray-600 mb-2">${label}</p>
                <table class="text-sm">
                    <tr><td class="pr-2"><strong>E:</strong></td><td>${parseFloat(measurement.E).toFixed(3)}</td></tr>
                    <tr><td class="pr-2"><strong>N:</strong></td><td>${parseFloat(measurement.N).toFixed(3)}</td></tr>
                    <tr><td class="pr-2"><strong>H:</strong></td><td>${parseFloat(measurement.H).toFixed(3)}</td></tr>
                    <tr><td class="pr-2"><strong>Datum:</strong></td><td>${measurement.date}</td></tr>
                </table>
            </div>
        `;
    }

    function addMarkersToMap(measurements, color, label) {
        const bounds = [];
        measurements.forEach(measurement => {
            const icon = createCustomIcon(color);
            const marker = L.marker([measurement.lat, measurement.lng], { icon: icon }).addTo(map);
            marker.bindPopup(createPopupContent(measurement, label));
            bounds.push([measurement.lat, measurement.lng]);
        });
        return bounds;
    }

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
                let allBounds = [];
                
                // Display null measurements (blue markers)
                if (data.nullMeasurements && data.nullMeasurements.length > 0) {
                    allBounds = allBounds.concat(addMarkersToMap(data.nullMeasurements, '#3b82f6', 'Nullmessung'));
                }

                // Display latest control measurements (red markers)
                if (data.controlMeasurements && data.controlMeasurements.length > 0) {
                    allBounds = allBounds.concat(addMarkersToMap(data.controlMeasurements, '#ef4444', 'Letzte Kontrollmessung'));
                }

                // Fit map to show all markers
                if (allBounds.length > 0) {
                    map.fitBounds(allBounds, { padding: [50, 50] });
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
