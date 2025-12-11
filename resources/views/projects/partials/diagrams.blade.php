<div>
    <h3 class="text-xl font-bold mb-4">Diagramme</h3>
    
    @php
        $controlByPoint = $project->controlMeasurements->groupBy('punkt')->sortKeys();
        $nullByPoint = $project->nullMeasurements->keyBy('punkt');
        $hasData = $controlByPoint->count() > 0 && $nullByPoint->count() > 0;
    @endphp
    
    @if($hasData)
        <!-- Chart 1: ΔE and ΔN over time -->
        <div class="mb-8 bg-white p-4 rounded border">
            <h4 class="font-bold mb-3">Diagramm 1: ΔE und ΔN je Punkt über Zeit</h4>
            <canvas id="chart-xy-shift" height="80"></canvas>
        </div>
        
        <!-- Chart 2: 2D Position shift over time -->
        <div class="mb-8 bg-white p-4 rounded border">
            <h4 class="font-bold mb-3">Diagramm 2: 2D Lageverschiebung je Punkt über Zeit</h4>
            <canvas id="chart-2d-shift" height="80"></canvas>
        </div>
        
        <!-- Chart 3: ΔH over time -->
        <div class="mb-8 bg-white p-4 rounded border">
            <h4 class="font-bold mb-3">Diagramm 3: ΔH je Punkt über Zeit</h4>
            <canvas id="chart-h-shift" height="80"></canvas>
        </div>
        
        <!-- Chart 4: Vector plot -->
        <div class="mb-8 bg-white p-4 rounded border">
            <h4 class="font-bold mb-3">Diagramm 4: Verschiebung als Vektoren (XY)</h4>
            <canvas id="chart-xy-vector" height="100"></canvas>
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
                    
                    foreach ($sortedMeasurements as $measurement) {
                        $dE = ($measurement->E - $nullMeasurement->E) * 1000;
                        $dN = ($measurement->N - $nullMeasurement->N) * 1000;
                        $dL = sqrt(pow($dE, 2) + pow($dN, 2));
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
                    maintainAspectRatio: true,
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
                    maintainAspectRatio: true,
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
                    maintainAspectRatio: true,
                    scales: {
                        x: { type: 'time', time: { unit: 'day' }, title: { display: true, text: 'Datum' } },
                        y: { title: { display: true, text: 'Höhenabweichung (mm)' } }
                    }
                }
            });
            
            // Chart 4: Vector plot
            const ctx4 = document.getElementById('chart-xy-vector').getContext('2d');
            
            // Calculate center and bounds
            let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
            vectorData.forEach(v => {
                minX = Math.min(minX, v.x0, v.x1);
                maxX = Math.max(maxX, v.x0, v.x1);
                minY = Math.min(minY, v.y0, v.y1);
                maxY = Math.max(maxY, v.y0, v.y1);
            });
            
            const centerX = (minX + maxX) / 2;
            const centerY = (minY + maxY) / 2;
            
            // Create scatter datasets for start and end points
            const vectorDatasets = [];
            vectorData.forEach((v, index) => {
                const dx = (v.x1 - v.x0) * 1000; // in mm
                const dy = (v.y1 - v.y0) * 1000; // in mm
                
                vectorDatasets.push({
                    label: v.punkt,
                    data: [
                        { x: (v.x0 - centerX) * 1000, y: (v.y0 - centerY) * 1000 },
                        { x: (v.x1 - centerX) * 1000, y: (v.y1 - centerY) * 1000 }
                    ],
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length],
                    showLine: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                });
            });
            
            new Chart(ctx4, {
                type: 'scatter',
                data: { datasets: vectorDatasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: { title: { display: true, text: 'ΔE (mm) von Zentrum' } },
                        y: { title: { display: true, text: 'ΔN (mm) von Zentrum' } }
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
