<div>
    <h3 class="text-xl font-bold mb-4">Abweichungstabellen</h3>
    
    <div class="mb-4 bg-gray-100 p-4 rounded">
        <h4 class="font-bold mb-2">Schwellenwerte:</h4>
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <span class="inline-block w-6 h-6 threshold-warning border mr-2"></span>
                Aufmerksamkeitswert: {{ $project->threshold_warning }} mm
            </div>
            <div>
                <span class="inline-block w-6 h-6 threshold-caution border mr-2"></span>
                Interventionswert: {{ $project->threshold_caution }} mm
            </div>
            <div>
                <span class="inline-block w-6 h-6 threshold-alarm border mr-2"></span>
                Alarmwert: {{ $project->threshold_alarm }} mm
            </div>
        </div>
    </div>

    @php
        // Group control measurements by punkt
        $controlByPoint = $project->controlMeasurements->groupBy('punkt')->sortKeys();
        $nullByPoint = $project->nullMeasurements->keyBy('punkt');
        
        // Helper function for threshold class
        $getThresholdClass = function($value, $warning, $caution, $alarm) {
            $abs = \abs($value);
            if ($abs >= $alarm) return 'threshold-alarm';
            if ($abs >= $caution) return 'threshold-caution';
            if ($abs >= $warning) return 'threshold-warning';
            return '';
        };
    @endphp

    @if($controlByPoint->count() > 0 && $nullByPoint->count() > 0)
        @foreach($controlByPoint as $punkt => $measurements)
            @if(isset($nullByPoint[$punkt]))
                @php
                    $nullMeasurement = $nullByPoint[$punkt];
                    $sortedMeasurements = $measurements->sortBy('date');
                @endphp
                
                <div class="mb-6 border rounded-lg p-4">
                    <h4 class="text-lg font-bold mb-3">Punkt: {{ $punkt }}</h4>
                    
                    <table class="w-full text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-3 py-2">Vergleich</th>
                                <th class="px-3 py-2">Kontrolldatum</th>
                                <th class="px-3 py-2">ΔE (mm)</th>
                                <th class="px-3 py-2">ΔN (mm)</th>
                                <th class="px-3 py-2">ΔL (mm)</th>
                                <th class="px-3 py-2">ΔH (mm)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Nullmessung row (baseline with 0 deviations) -->
                            <tr class="border-b bg-blue-50">
                                <td class="px-3 py-2 font-bold">Nullmessung</td>
                                <td class="px-3 py-2">{{ $nullMeasurement->date->format('d.m.Y') }}</td>
                                <td class="px-3 py-2">0.00</td>
                                <td class="px-3 py-2">0.00</td>
                                <td class="px-3 py-2">0.00</td>
                                <td class="px-3 py-2">0.00</td>
                            </tr>
                            
                            @php $prevMeasurement = null; @endphp
                            @foreach($sortedMeasurements as $measurement)
                                @php
                                    // Calculate deviations from null measurement
                                    $dE_null = ($measurement->E - $nullMeasurement->E) * 1000;
                                    $dN_null = ($measurement->N - $nullMeasurement->N) * 1000;
                                    $dL_null = \sqrt(\pow($dE_null, 2) + \pow($dN_null, 2));
                                    $dH_null = ($measurement->H - $nullMeasurement->H) * 1000;
                                @endphp
                                
                                <!-- Null measurement comparison -->
                                <tr class="border-b">
                                    <td class="px-3 py-2">Nullmessung</td>
                                    <td class="px-3 py-2">{{ $measurement->date->format('d.m.Y') }}</td>
                                    <td class="px-3 py-2 {{ $getThresholdClass($dE_null, $project->threshold_warning, $project->threshold_caution, $project->threshold_alarm) }}">
                                        {{ number_format($dE_null, 2) }}
                                    </td>
                                    <td class="px-3 py-2 {{ $getThresholdClass($dN_null, $project->threshold_warning, $project->threshold_caution, $project->threshold_alarm) }}">
                                        {{ number_format($dN_null, 2) }}
                                    </td>
                                    <td class="px-3 py-2 {{ $getThresholdClass($dL_null, $project->threshold_warning, $project->threshold_caution, $project->threshold_alarm) }}">
                                        {{ number_format($dL_null, 2) }}
                                    </td>
                                    <td class="px-3 py-2 {{ $getThresholdClass($dH_null, $project->threshold_warning, $project->threshold_caution, $project->threshold_alarm) }}">
                                        {{ number_format($dH_null, 2) }}
                                    </td>
                                </tr>
                                
                                @if($prevMeasurement !== null)
                                    @php
                                        // Calculate deviations from previous measurement
                                        $dE_prev = ($measurement->E - $prevMeasurement->E) * 1000;
                                        $dN_prev = ($measurement->N - $prevMeasurement->N) * 1000;
                                        $dL_prev = \sqrt(\pow($dE_prev, 2) + \pow($dN_prev, 2));
                                        $dH_prev = ($measurement->H - $prevMeasurement->H) * 1000;
                                    @endphp
                                    
                                    <!-- Previous measurement comparison -->
                                    <tr class="border-b">
                                        <td class="px-3 py-2">Vormessung</td>
                                        <td class="px-3 py-2">{{ $measurement->date->format('d.m.Y') }}</td>
                                        <td class="px-3 py-2">{{ number_format($dE_prev, 2) }}</td>
                                        <td class="px-3 py-2">{{ number_format($dN_prev, 2) }}</td>
                                        <td class="px-3 py-2">{{ number_format($dL_prev, 2) }}</td>
                                        <td class="px-3 py-2">{{ number_format($dH_prev, 2) }}</td>
                                    </tr>
                                @endif
                                
                                <!-- Separator row -->
                                <tr class="border-b bg-gray-200">
                                    <td colspan="6" class="px-3 py-1"></td>
                                </tr>
                                
                                @php $prevMeasurement = $measurement; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach
    @else
        <p class="text-gray-600">Keine Daten für Abweichungstabellen verfügbar. Bitte importieren Sie zunächst Null- und Kontrollmessungen.</p>
    @endif
    
    @if($controlByPoint->count() > 0 && $nullByPoint->count() > 0)
        <div class="mt-6">
            <a href="{{ route('reports.pdf', $project) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded inline-block">
                PDF-Bericht erstellen
            </a>
        </div>
    @endif
</div>
