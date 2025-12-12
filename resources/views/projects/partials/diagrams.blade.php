<div>
    <h3 class="text-lg sm:text-xl font-bold mb-4">Diagramme</h3>
    
    @php
        $controlByPoint = $project->controlMeasurements->groupBy('punkt')->sortKeys();
        $nullByPoint = $project->nullMeasurements->keyBy('punkt');
        $hasData = $controlByPoint->count() > 0 && $nullByPoint->count() > 0;
    @endphp
    
    @if($hasData)
        <!-- Chart 1: ΔE and ΔN over time -->
        <div class="mb-6 sm:mb-8 bg-white p-3 sm:p-4 rounded border">
            <h4 class="font-bold mb-3 text-sm sm:text-base">Diagramm 1: ΔE und ΔN je Punkt über Zeit</h4>
            <div class="relative" style="height: 300px;">
                <canvas id="chart-xy-shift"></canvas>
            </div>
        </div>
        
        <!-- Chart 2: 2D Position shift over time -->
        <div class="mb-6 sm:mb-8 bg-white p-3 sm:p-4 rounded border">
            <h4 class="font-bold mb-3 text-sm sm:text-base">Diagramm 2: 2D Lageverschiebung je Punkt über Zeit</h4>
            <div class="relative" style="height: 300px;">
                <canvas id="chart-2d-shift"></canvas>
            </div>
        </div>
        
        <!-- Chart 3: ΔH over time -->
        <div class="mb-6 sm:mb-8 bg-white p-3 sm:p-4 rounded border">
            <h4 class="font-bold mb-3 text-sm sm:text-base">Diagramm 3: ΔH je Punkt über Zeit</h4>
            <div class="relative" style="height: 300px;">
                <canvas id="chart-h-shift"></canvas>
            </div>
        </div>
        
        <!-- Chart 4: Vector plot -->
        <div class="mb-6 sm:mb-8 bg-white p-3 sm:p-4 rounded border">
            <h4 class="font-bold mb-3 text-sm sm:text-base">Diagramm 4: Verschiebung als Vektoren (XY)</h4>
            <div class="relative" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <div style="position: relative; width: 100%; padding-bottom: 100%;">
                    <canvas id="chart-xy-vector" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></canvas>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            @php
                $chartData = [];
                $vectorData = [];
                
                foreach ($controlByPoint as $punkt => $measurements) {
                    if (!isset($nullByPoint[$punkt])) continue;
                    
                    $nullMeasurement = $nullByPoint[$punkt];
                    $sortedMeasurements = $measurements->sortBy('date');
                    
                    $pointData = [
                        'punkt' => $punkt,
                        'dates' => [],
                        'dE' => [],
                        'dN' => [],
                        'dL' => [],
                        'dH' => [],
                    ];
                    
                    // Add null measurement as the first data point (0 deviation)
                    $pointData['dates'][] = $nullMeasurement->date->format('Y-m-d');
                    $pointData['dE'][] = round(0, 2);
                    $pointData['dN'][] = round(0, 2);
                    $pointData['dL'][] = round(0, 2);
                    $pointData['dH'][] = round(0, 2);
                    
                    foreach ($sortedMeasurements as $measurement) {
                        $dE = ($measurement->E - $nullMeasurement->E) * 1000;
                        $dN = ($measurement->N - $nullMeasurement->N) * 1000;
                        $dL = \sqrt(\pow($dE, 2) + \pow($dN, 2));
                        $dH = ($measurement->H - $nullMeasurement->H) * 1000;
                        
                        $pointData['dates'][] = $measurement->date->format('Y-m-d');
                        $pointData['dE'][] = round($dE, 2);
                        $pointData['dN'][] = round($dN, 2);
                        $pointData['dL'][] = round($dL, 2);
                        $pointData['dH'][] = round($dH, 2);
                    }
                    
                    $chartData[] = $pointData;
                    
                    // Get latest measurement for vector plot
                    $latestMeasurement = $sortedMeasurements->last();
                    $vectorData[] = [
                        'punkt' => $punkt,
                        'x0' => round($nullMeasurement->E, 3),
                        'y0' => round($nullMeasurement->N, 3),
                        'x1' => round($latestMeasurement->E, 3),
                        'y1' => round($latestMeasurement->N, 3),
                    ];
                }
            @endphp
            
            const chartData = @json($chartData);
            const vectorData = @json($vectorData);
            
            const colors = [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 206, 86)',
                'rgb(75, 192, 192)',
                'rgb(153, 102, 255)',
                'rgb(255, 159, 64)',
                'rgb(199, 199, 199)',
                'rgb(83, 102, 255)',
                'rgb(255, 99, 255)',
                'rgb(99, 255, 132)'
            ];
            
            // Chart 1: ΔE and ΔN
            const ctx1 = document.getElementById('chart-xy-shift').getContext('2d');
            const datasets1 = [];
            chartData.forEach((point, index) => {
                datasets1.push({
                    label: point.punkt + ' (ΔE)',
                    data: point.dates.map((date, i) => ({ x: date, y: point.dE[i] })),
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length] + '40',
                    borderDash: [5, 5],
                });
                datasets1.push({
                    label: point.punkt + ' (ΔN)',
                    data: point.dates.map((date, i) => ({ x: date, y: point.dN[i] })),
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length] + '40',
                });
            });
            
            new Chart(ctx1, {
                type: 'line',
                data: { datasets: datasets1 },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { type: 'time', time: { unit: 'day' }, title: { display: true, text: 'Datum' } },
                        y: { title: { display: true, text: 'Abweichung (mm)' } }
                    }
                }
            });
            
            // Chart 2: 2D shift
            const ctx2 = document.getElementById('chart-2d-shift').getContext('2d');
            const datasets2 = [];
            chartData.forEach((point, index) => {
                datasets2.push({
                    label: point.punkt + ' (ΔL)',
                    data: point.dates.map((date, i) => ({ x: date, y: point.dL[i] })),
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length] + '40',
                });
            });
            
            new Chart(ctx2, {
                type: 'line',
                data: { datasets: datasets2 },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { type: 'time', time: { unit: 'day' }, title: { display: true, text: 'Datum' } },
                        y: { title: { display: true, text: '2D Verschiebung (mm)' }, beginAtZero: true }
                    }
                }
            });
            
            // Chart 3: ΔH
            const ctx3 = document.getElementById('chart-h-shift').getContext('2d');
            const datasets3 = [];
            chartData.forEach((point, index) => {
                datasets3.push({
                    label: point.punkt + ' (ΔH)',
                    data: point.dates.map((date, i) => ({ x: date, y: point.dH[i] })),
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length] + '40',
                });
            });
            
            new Chart(ctx3, {
                type: 'line',
                data: { datasets: datasets3 },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { type: 'time', time: { unit: 'day' }, title: { display: true, text: 'Datum' } },
                        y: { title: { display: true, text: 'Höhenabweichung (mm)' } }
                    }
                }
            });
            
            // Chart 4: Displacement endpoints (Scatter Chart)
            const ctx4 = document.getElementById('chart-xy-vector').getContext('2d');
            
            // Create scatter datasets for all displacement points
            const vectorDatasets = [];
            chartData.forEach((point, index) => {
                // Create array of all displacement points for this survey point
                const displacementPoints = point.dE.map((dE, i) => ({
                    x: dE,
                    y: point.dN[i]
                }));
                
                vectorDatasets.push({
                    label: point.punkt,
                    data: displacementPoints,
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length],
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    showLine: true,
                    fill: false,
                    tension: 0.1,
                });
            });
            
            // Calculate symmetric axis ranges to center (0,0)
            let maxAbsX = 0;
            let maxAbsY = 0;
            chartData.forEach(point => {
                maxAbsX = Math.max(maxAbsX, ...point.dE.map(Math.abs));
                maxAbsY = Math.max(maxAbsY, ...point.dN.map(Math.abs));
            });
            
            // Use the same range for both axes to ensure square appearance
            const maxAbs = Math.max(maxAbsX, maxAbsY);
            const axisRange = maxAbs > 0 ? maxAbs * 1.1 : 10; // Add 10% padding, or default to 10 if no data
            
            new Chart(ctx4, {
                type: 'scatter',
                data: { datasets: vectorDatasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1,
                    scales: {
                        x: { 
                            title: { display: true, text: 'ΔE (mm)' },
                            min: -axisRange,
                            max: axisRange
                        },
                        y: { 
                            title: { display: true, text: 'ΔN (mm)' },
                            min: -axisRange,
                            max: axisRange
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': (' + 
                                           context.parsed.x.toFixed(2) + ', ' + 
                                           context.parsed.y.toFixed(2) + ')';
                                }
                            }
                        }
                    }
                }
            });
        });
        </script>
    @else
        <p class="text-gray-600">Keine Daten für Diagramme verfügbar. Bitte importieren Sie zunächst Null- und Kontrollmessungen.</p>
    @endif
</div>
