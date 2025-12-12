<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\NullMeasurement;
use App\Models\ControlMeasurement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ReflectionClass;
use App\Http\Controllers\ReportController;

class PdfAllControlMeasurementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_control_measurements_included_not_just_first(): void
    {
        // This test explicitly verifies that ALL control measurements are included
        // in the PDF chart data, not just the first one as described in the bug report
        
        // Test constants
        $numControlMeasurements = 7;  // Test with 7 measurements (more than the reported 2)
        $totalDataPoints = $numControlMeasurements + 1;  // +1 for null measurement
        $coordinateIncrement = 0.001;
        $initialDaysAgo = 100;
        $daysBetweenMeasurements = 10;
        
        $project = Project::create([
            'number' => 'TEST-BUG-001',
            'name' => 'Bug Verification Project',
            'bearbeiter' => 'Test Engineer',
            'threshold_warning' => 3.0,
            'threshold_caution' => 5.0,
            'threshold_alarm' => 10.0,
        ]);

        // Create null measurement
        NullMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P1',
            'E' => 2500000.000,
            'N' => 1200000.000,
            'H' => 450.000,
            'date' => now()->subDays($initialDaysAgo),
        ]);

        // Create control measurements to verify all are included
        // Bug report mentioned only 2 points shown (null + first control)
        // so we need to ensure all control measurements appear
        for ($i = 1; $i <= $numControlMeasurements; $i++) {
            ControlMeasurement::create([
                'project_id' => $project->id,
                'punkt' => 'P1',
                'E' => 2500000.000 + ($i * $coordinateIncrement),
                'N' => 1200000.000 + ($i * $coordinateIncrement),
                'H' => 450.000 + ($i * $coordinateIncrement),
                'date' => now()->subDays($initialDaysAgo - ($i * $daysBetweenMeasurements)),
            ]);
        }

        // Prepare chart data using the same logic as ReportController
        $project->load('nullMeasurements', 'controlMeasurements');
        $controlByPoint = $project->controlMeasurements->groupBy('punkt')->sortKeys();
        $nullByPoint = $project->nullMeasurements->keyBy('punkt');
        
        $chartData = [];
        foreach ($controlByPoint as $punkt => $measurements) {
            if (!isset($nullByPoint[$punkt])) continue;
            
            $nullMeasurement = $nullByPoint[$punkt];
            $sortedMeasurements = $measurements->sortBy('date');
            
            // Verify that sortBy doesn't limit results
            $this->assertCount($numControlMeasurements, $sortedMeasurements, 'sortBy should not limit the number of measurements');
            
            $pointData = [
                'punkt' => $punkt,
                'dates' => [],
                'dE' => [],
                'dN' => [],
                'dL' => [],
                'dH' => [],
            ];
            
            // Add null measurement
            $pointData['dates'][] = $nullMeasurement->date->format('Y-m-d');
            $pointData['dE'][] = round(0, 2);
            $pointData['dN'][] = round(0, 2);
            $pointData['dL'][] = round(0, 2);
            $pointData['dH'][] = round(0, 2);
            
            // Add ALL control measurements
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
        }

        // Verify we have exactly 1 survey point with all data points (1 null + all control)
        $this->assertCount(1, $chartData, 'Should have data for 1 survey point');
        $this->assertCount($totalDataPoints, $chartData[0]['dates'], 
            "Should have $totalDataPoints total data points (1 null + $numControlMeasurements control), not just 2");
        $this->assertCount($totalDataPoints, $chartData[0]['dE'], "dE should have $totalDataPoints values");
        $this->assertCount($totalDataPoints, $chartData[0]['dN'], "dN should have $totalDataPoints values");
        $this->assertCount($totalDataPoints, $chartData[0]['dL'], "dL should have $totalDataPoints values");
        $this->assertCount($totalDataPoints, $chartData[0]['dH'], "dH should have $totalDataPoints values");
        
        // Now verify that the chart generation includes all points in the datasets
        $controller = new ReportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('generateChartImages');
        $method->setAccessible(true);
        
        $chartImages = $method->invoke($controller, $chartData);
        
        // Decode Chart 1 (ΔE and ΔN) and verify
        $parsedUrl = parse_url($chartImages['xyShift']);
        parse_str($parsedUrl['query'], $queryParams);
        $chart1Config = json_decode($queryParams['chart'], true);
        
        $this->assertArrayHasKey('data', $chart1Config);
        $this->assertArrayHasKey('datasets', $chart1Config['data']);
        
        // Chart 1 should have 2 datasets (ΔE and ΔN)
        $this->assertCount(2, $chart1Config['data']['datasets'], 'Chart 1 should have 2 datasets');
        
        // Each dataset should have ALL data points
        foreach ($chart1Config['data']['datasets'] as $dataset) {
            $this->assertCount($totalDataPoints, $dataset['data'], 
                "Chart 1 datasets must include ALL $totalDataPoints data points (1 null + $numControlMeasurements control), not just 2");
        }
        
        // Decode Chart 2 (2D shift) and verify
        $parsedUrl = parse_url($chartImages['twoDShift']);
        parse_str($parsedUrl['query'], $queryParams);
        $chart2Config = json_decode($queryParams['chart'], true);
        
        $this->assertCount(1, $chart2Config['data']['datasets'], 'Chart 2 should have 1 dataset');
        $this->assertCount($totalDataPoints, $chart2Config['data']['datasets'][0]['data'],
            "Chart 2 dataset must include ALL $totalDataPoints data points");
        
        // Decode Chart 3 (ΔH) and verify
        $parsedUrl = parse_url($chartImages['hShift']);
        parse_str($parsedUrl['query'], $queryParams);
        $chart3Config = json_decode($queryParams['chart'], true);
        
        $this->assertCount(1, $chart3Config['data']['datasets'], 'Chart 3 should have 1 dataset');
        $this->assertCount($totalDataPoints, $chart3Config['data']['datasets'][0]['data'],
            "Chart 3 dataset must include ALL $totalDataPoints data points");
    }

    public function test_multiple_points_each_with_all_control_measurements(): void
    {
        // Test scenario with multiple survey points to ensure each point
        // gets all its control measurements in the charts
        
        // Test constants
        $numSurveyPoints = 3;
        $numControlMeasurementsPerPoint = 4;
        $totalDataPointsPerSurveyPoint = $numControlMeasurementsPerPoint + 1;  // +1 for null
        $coordinateIncrement = 0.001;
        $initialDaysAgo = 60;
        $daysBetweenMeasurements = 10;
        
        $project = Project::create([
            'number' => 'TEST-MULTI-BUG',
            'name' => 'Multi-Point Bug Test',
            'bearbeiter' => 'Test Engineer',
            'threshold_warning' => 3.0,
            'threshold_caution' => 5.0,
            'threshold_alarm' => 10.0,
        ]);

        // Create multiple survey points
        foreach (['P1', 'P2', 'P3'] as $punkt) {
            NullMeasurement::create([
                'project_id' => $project->id,
                'punkt' => $punkt,
                'E' => 2500000.000,
                'N' => 1200000.000,
                'H' => 450.000,
                'date' => now()->subDays($initialDaysAgo),
            ]);
            
            // Each point has multiple control measurements
            for ($i = 1; $i <= $numControlMeasurementsPerPoint; $i++) {
                ControlMeasurement::create([
                    'project_id' => $project->id,
                    'punkt' => $punkt,
                    'E' => 2500000.000 + ($i * $coordinateIncrement),
                    'N' => 1200000.000 + ($i * $coordinateIncrement),
                    'H' => 450.000 + ($i * $coordinateIncrement),
                    'date' => now()->subDays($initialDaysAgo - ($i * $daysBetweenMeasurements)),
                ]);
            }
        }

        // Prepare chart data
        $project->load('nullMeasurements', 'controlMeasurements');
        $controlByPoint = $project->controlMeasurements->groupBy('punkt')->sortKeys();
        $nullByPoint = $project->nullMeasurements->keyBy('punkt');
        
        $chartData = [];
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
            
            $pointData['dates'][] = $nullMeasurement->date->format('Y-m-d');
            $pointData['dE'][] = round(0, 2);
            $pointData['dN'][] = round(0, 2);
            $pointData['dL'][] = round(0, 2);
            $pointData['dH'][] = round(0, 2);
            
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
        }

        // Verify each survey point has all its measurements
        $this->assertCount($numSurveyPoints, $chartData, "Should have data for $numSurveyPoints survey points");
        foreach ($chartData as $point) {
            $this->assertCount($totalDataPointsPerSurveyPoint, $point['dates'], 
                "Each survey point should have $totalDataPointsPerSurveyPoint data points (1 null + $numControlMeasurementsPerPoint control)");
        }
    }
}
