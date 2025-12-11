<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Überwachungsmessungs-Bericht</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }
        h1 {
            text-align: center;
            color: #003366;
            font-size: 20pt;
            margin-top: 100px;
        }
        h2 {
            color: #003366;
            font-size: 14pt;
            margin-top: 20px;
            page-break-before: auto;
        }
        h3 {
            color: #003366;
            font-size: 12pt;
            margin-top: 15px;
        }
        .company {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info {
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8pt;
        }
        table th {
            background-color: #003366;
            color: white;
            padding: 6px;
            text-align: center;
            font-weight: bold;
        }
        table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        .threshold-warning {
            background-color: #FFFF00;
        }
        .threshold-caution {
            background-color: #FFA500;
        }
        .threshold-alarm {
            background-color: #FF0000;
            color: white;
        }
        .separator {
            background-color: #E8E8E8;
        }
        .page-break {
            page-break-after: always;
        }
        .chart-note {
            font-style: italic;
            color: #666;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <!-- Title Page -->
    <div class="page-break">
        <div class="company">Ihr Unternehmen</div>
        <h1>Überwachungsmessungs-Bericht</h1>
        <div style="margin-top: 80px;">
            <div class="info"><strong>Projekt:</strong> {{ $project->name }}</div>
            <div class="info"><strong>Projektnummer:</strong> {{ $project->number }}</div>
            <div class="info"><strong>Bearbeiter:</strong> {{ $project->bearbeiter }}</div>
            <div class="info"><strong>Datum:</strong> {{ date('d.m.Y') }}</div>
        </div>
    </div>

    <!-- Charts Note -->
    <h2>Diagramme</h2>
    <p class="chart-note">
        Hinweis: Diagramme werden in der Web-Ansicht angezeigt. 
        Für detaillierte Visualisierungen verwenden Sie bitte die Diagramm-Ansicht in der Anwendung.
    </p>
    
    @if(count($chartData) > 0)
        <p>Übersicht der erfassten Messpunkte:</p>
        <ul>
            @foreach($chartData as $point)
                <li>Punkt {{ $point['punkt'] }}: {{ count($point['dates']) }} Messungen</li>
            @endforeach
        </ul>
    @endif

    <!-- Deviation Tables -->
    <div class="page-break"></div>
    <h2>Abweichungstabellen</h2>
    
    <div style="margin: 10px 0; padding: 10px; background-color: #f0f0f0;">
        <strong>Schwellenwerte:</strong><br>
        <span style="display: inline-block; width: 20px; height: 20px; background-color: #FFFF00; border: 1px solid #000; vertical-align: middle;"></span>
        Aufmerksamkeitswert: {{ $project->threshold_warning }} mm &nbsp;&nbsp;
        <span style="display: inline-block; width: 20px; height: 20px; background-color: #FFA500; border: 1px solid #000; vertical-align: middle;"></span>
        Interventionswert: {{ $project->threshold_caution }} mm &nbsp;&nbsp;
        <span style="display: inline-block; width: 20px; height: 20px; background-color: #FF0000; border: 1px solid #000; vertical-align: middle;"></span>
        Alarmwert: {{ $project->threshold_alarm }} mm
    </div>

    @foreach($controlByPoint as $punkt => $measurements)
        @if(isset($nullByPoint[$punkt]))
            @php
                $nullMeasurement = $nullByPoint[$punkt];
                $sortedMeasurements = $measurements->sortBy('date');
            @endphp
            
            <h3>Punkt: {{ $punkt }}</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Vergleich</th>
                        <th>Kontrolldatum</th>
                        <th>ΔE (mm)</th>
                        <th>ΔN (mm)</th>
                        <th>ΔL (mm)</th>
                        <th>ΔH (mm)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $prevMeasurement = null; @endphp
                    @foreach($sortedMeasurements as $measurement)
                        @php
                            // Calculate deviations from null measurement
                            $dE_null = ($measurement->E - $nullMeasurement->E) * 1000;
                            $dN_null = ($measurement->N - $nullMeasurement->N) * 1000;
                            $dL_null = sqrt(pow($dE_null, 2) + pow($dN_null, 2));
                            $dH_null = ($measurement->H - $nullMeasurement->H) * 1000;
                            
                            function getThresholdClass($value, $warning, $caution, $alarm) {
                                $abs = abs($value);
                                if ($abs >= $alarm) return 'threshold-alarm';
                                if ($abs >= $caution) return 'threshold-caution';
                                if ($abs >= $warning) return 'threshold-warning';
                                return '';
                            }
                        @endphp
                        
                        <!-- Null measurement comparison -->
                        <tr>
                            <td>Nullmessung</td>
                            <td>{{ $measurement->date->format('d.m.Y') }}</td>
                            <td class="{{ getThresholdClass($dE_null, $project->threshold_warning, $project->threshold_caution, $project->threshold_alarm) }}">
                                {{ number_format($dE_null, 2) }}
                            </td>
                            <td class="{{ getThresholdClass($dN_null, $project->threshold_warning, $project->threshold_caution, $project->threshold_alarm) }}">
                                {{ number_format($dN_null, 2) }}
                            </td>
                            <td class="{{ getThresholdClass($dL_null, $project->threshold_warning, $project->threshold_caution, $project->threshold_alarm) }}">
                                {{ number_format($dL_null, 2) }}
                            </td>
                            <td class="{{ getThresholdClass($dH_null, $project->threshold_warning, $project->threshold_caution, $project->threshold_alarm) }}">
                                {{ number_format($dH_null, 2) }}
                            </td>
                        </tr>
                        
                        @if($prevMeasurement !== null)
                            @php
                                // Calculate deviations from previous measurement
                                $dE_prev = ($measurement->E - $prevMeasurement->E) * 1000;
                                $dN_prev = ($measurement->N - $prevMeasurement->N) * 1000;
                                $dL_prev = sqrt(pow($dE_prev, 2) + pow($dN_prev, 2));
                                $dH_prev = ($measurement->H - $prevMeasurement->H) * 1000;
                            @endphp
                            
                            <!-- Previous measurement comparison -->
                            <tr>
                                <td>Vormessung</td>
                                <td>{{ $measurement->date->format('d.m.Y') }}</td>
                                <td>{{ number_format($dE_prev, 2) }}</td>
                                <td>{{ number_format($dN_prev, 2) }}</td>
                                <td>{{ number_format($dL_prev, 2) }}</td>
                                <td>{{ number_format($dH_prev, 2) }}</td>
                            </tr>
                        @endif
                        
                        <!-- Separator row -->
                        <tr class="separator">
                            <td colspan="6">&nbsp;</td>
                        </tr>
                        
                        @php $prevMeasurement = $measurement; @endphp
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</body>
</html>
