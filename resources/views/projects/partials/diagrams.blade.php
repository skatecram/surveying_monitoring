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
                    
                    // Collect all measurements for vector plot (including null measurement)
                    $pointVectorData = [
                        'punkt' => $punkt,
                        'measurements' => []
                    ];
                    
                    // Add null measurement as the reference point
                    $pointVectorData['measurements'][] = [
                        'E' => round($nullMeasurement->E, 3),
                        'N' => round($nullMeasurement->N, 3),
                        'date' => $nullMeasurement->date->format('Y-m-d'),
                        'isNull' => true
                    ];
                    
                    // Add all control measurements
                    foreach ($sortedMeasurements as $measurement) {
                        $pointVectorData['measurements'][] = [
                            'E' => round($measurement->E, 3),
                            'N' => round($measurement->N, 3),
                            'date' => $measurement->date->format('Y-m-d'),
                            'isNull' => false
                        ];
                    }
                    
                    $vectorData[] = $pointVectorData;
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
            
            // Calculate center and bounds from all measurements
            let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
            vectorData.forEach(pointData => {
                pointData.measurements.forEach(m => {
                    minX = Math.min(minX, m.E);
                    maxX = Math.max(maxX, m.E);
                    minY = Math.min(minY, m.N);
                    maxY = Math.max(maxY, m.N);
                });
            });
            
            const centerX = (minX + maxX) / 2;
            const centerY = (minY + maxY) / 2;
            
            // Create scatter datasets for all measurements
            const vectorDatasets = [];
            vectorData.forEach((pointData, index) => {
                const measurements = pointData.measurements;
                const color = colors[index % colors.length];
                
                // Create data points for all measurements
                const dataPoints = measurements.map(m => ({
                    x: (m.E - centerX) * 1000,
                    y: (m.N - centerY) * 1000,
                    date: m.date,
                    isNull: m.isNull
                }));
                
                // Add dataset with lines connecting the measurements
                vectorDatasets.push({
                    label: pointData.punkt,
                    data: dataPoints,
                    borderColor: color,
                    backgroundColor: color,
                    showLine: true,
                    pointRadius: dataPoints.map(p => p.isNull ? 8 : 5),
                    pointStyle: dataPoints.map(p => p.isNull ? 'rectRot' : 'circle'),
                    pointHoverRadius: 9,
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
                                    const dataPoint = context.dataset.data[context.dataIndex];
                                    const type = dataPoint.isNull ? ' (Nullmessung)' : '';
                                    return context.dataset.label + type + ': (' + 
                                           context.parsed.x.toFixed(2) + ', ' + 
                                           context.parsed.y.toFixed(2) + ') - ' + dataPoint.date;
                                }
                            }
                        },
                        legend: {
                            display: true,
                            labels: {
                                generateLabels: function(chart) {
                                    const original = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                    return original;
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
