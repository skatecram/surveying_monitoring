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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map only when the map tab is shown
    let mapInitialized = false;
    let map = null;

    function initializeMap() {
        if (mapInitialized) return;
        
        // Create the map centered on Switzerland
        map = L.map('map').setView([46.8182, 8.2275], 8);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
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
                // Small delay to ensure the map container is visible
                setTimeout(initializeMap, 100);
            }
        };
    }
});
</script>
@endif
