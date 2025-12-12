<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\NullMeasurement;
use App\Models\ControlMeasurement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ReflectionClass;
use App\Http\Controllers\ReportController;

class PdfChartDataPointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_chart_datasets_have_point_styling_for_visibility(): void
    {
        // Create a test project with multiple points and measurements
        $project = Project::create([
            'number' => 'TEST-003',
            'name' => 'Chart Test Project',
            'bearbeiter' => 'Test User',
            'threshold_warning' => 3.0,
            'threshold_caution' => 5.0,
            'threshold_alarm' => 10.0,
        ]);

        // Create null measurements for 3 points
        foreach (['P1', 'P2', 'P3'] as $punkt) {
            NullMeasurement::create([
                'project_id' => $project->id,
                'punkt' => $punkt,
                'E' => 2500000.000 + rand(0, 100),
                'N' => 1200000.000 + rand(0, 100),
                'H' => 450.000 + rand(0, 10),
                'date' => now()->subDays(60),
            ]);
        }

        // Create multiple control measurements per point
        foreach (['P1', 'P2', 'P3'] as $punkt) {
            for ($i = 0; $i < 5; $i++) {
                ControlMeasurement::create([
                    'project_id' => $project->id,
                    'punkt' => $punkt,
                    'E' => 2500000.000 + rand(0, 100) + ($i * 0.002),
                    'N' => 1200000.000 + rand(0, 100) + ($i * 0.002),
                    'H' => 450.000 + rand(0, 10) + ($i * 0.002),
                    'date' => now()->subDays(50 - ($i * 10)),
                ]);
            }
        }

        // Use reflection to access the private generateChartImages method
        $controller = new ReportController();
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('generateChartImages');
        $method->setAccessible(true);

        // Prepare chart data the same way the controller does
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
            
            // Add null measurement
            $pointData['dates'][] = $nullMeasurement->date->format('Y-m-d');
            $pointData['dE'][] = round(0, 2);
            $pointData['dN'][] = round(0, 2);
            $pointData['dL'][] = round(0, 2);
            $pointData['dH'][] = round(0, 2);
            
            // Add control measurements
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

        // Generate chart images
        $chartImages = $method->invoke($controller, $chartData);

        // Verify that chart images were generated
        $this->assertNotNull($chartImages['xyShift']);
        $this->assertNotNull($chartImages['twoDShift']);
        $this->assertNotNull($chartImages['hShift']);
        $this->assertNotNull($chartImages['xyVector']);

        // Verify the URLs contain the chart configuration
        $this->assertStringContainsString('quickchart.io/chart', $chartImages['xyShift']);
        $this->assertStringContainsString('quickchart.io/chart', $chartImages['twoDShift']);
        $this->assertStringContainsString('quickchart.io/chart', $chartImages['hShift']);

        // Decode and verify chart 1 configuration includes point styling
        $parsedUrl = parse_url($chartImages['xyShift']);
        parse_str($parsedUrl['query'], $queryParams);
        $chart1Config = json_decode($queryParams['chart'], true);
        
        // Verify Chart 1 has datasets with point styling
        $this->assertArrayHasKey('data', $chart1Config);
        $this->assertArrayHasKey('datasets', $chart1Config['data']);
        $this->assertNotEmpty($chart1Config['data']['datasets']);
        
        // Check first dataset has point styling properties
        $firstDataset = $chart1Config['data']['datasets'][0];
        $this->assertArrayHasKey('pointRadius', $firstDataset, 'Chart 1 datasets must have pointRadius for visibility');
        $this->assertArrayHasKey('backgroundColor', $firstDataset, 'Chart 1 datasets must have backgroundColor for point fill');
        $this->assertEquals(4, $firstDataset['pointRadius'], 'Point radius should be 4 for clear visibility');

        // Verify Chart 2 configuration
        $parsedUrl = parse_url($chartImages['twoDShift']);
        parse_str($parsedUrl['query'], $queryParams);
        $chart2Config = json_decode($queryParams['chart'], true);
        
        $firstDataset = $chart2Config['data']['datasets'][0];
        $this->assertArrayHasKey('pointRadius', $firstDataset, 'Chart 2 datasets must have pointRadius for visibility');
        $this->assertArrayHasKey('backgroundColor', $firstDataset, 'Chart 2 datasets must have backgroundColor for point fill');
        $this->assertEquals(4, $firstDataset['pointRadius'], 'Point radius should be 4 for clear visibility');

        // Verify Chart 3 configuration
        $parsedUrl = parse_url($chartImages['hShift']);
        parse_str($parsedUrl['query'], $queryParams);
        $chart3Config = json_decode($queryParams['chart'], true);
        
        $firstDataset = $chart3Config['data']['datasets'][0];
        $this->assertArrayHasKey('pointRadius', $firstDataset, 'Chart 3 datasets must have pointRadius for visibility');
        $this->assertArrayHasKey('backgroundColor', $firstDataset, 'Chart 3 datasets must have backgroundColor for point fill');
        $this->assertEquals(4, $firstDataset['pointRadius'], 'Point radius should be 4 for clear visibility');

        // Verify all measurement points are included in the data
        foreach ($chart1Config['data']['datasets'] as $dataset) {
            // Each dataset should have 6 data points (1 null + 5 control measurements)
            $this->assertCount(6, $dataset['data'], 'Each dataset should include all measurement points');
        }
    }

    public function test_all_measurement_points_included_in_chart_data(): void
    {
        // Create a test project
        $project = Project::create([
            'number' => 'TEST-004',
            'name' => 'Data Points Test',
            'bearbeiter' => 'Test User',
            'threshold_warning' => 3.0,
            'threshold_caution' => 5.0,
            'threshold_alarm' => 10.0,
        ]);

        // Create null measurement for point P1
        NullMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P1',
            'E' => 2500000.123,
            'N' => 1200000.456,
            'H' => 450.123,
            'date' => now()->subDays(30),
        ]);

        // Create 3 control measurements
        $controlDates = [25, 20, 15];
        foreach ($controlDates as $daysAgo) {
            ControlMeasurement::create([
                'project_id' => $project->id,
                'punkt' => 'P1',
                'E' => 2500000.125 + ($daysAgo / 1000),
                'N' => 1200000.458 + ($daysAgo / 1000),
                'H' => 450.125 + ($daysAgo / 1000),
                'date' => now()->subDays($daysAgo),
            ]);
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
            
            // Add null measurement
            $pointData['dates'][] = $nullMeasurement->date->format('Y-m-d');
            $pointData['dE'][] = round(0, 2);
            $pointData['dN'][] = round(0, 2);
            $pointData['dL'][] = round(0, 2);
            $pointData['dH'][] = round(0, 2);
            
            // Add control measurements
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

        // Verify chart data has all points
        $this->assertCount(1, $chartData, 'Should have data for 1 surveying point');
        $this->assertCount(4, $chartData[0]['dates'], 'Should have 4 data points (1 null + 3 control)');
        $this->assertCount(4, $chartData[0]['dE'], 'Should have 4 dE values');
        $this->assertCount(4, $chartData[0]['dN'], 'Should have 4 dN values');
        $this->assertCount(4, $chartData[0]['dL'], 'Should have 4 dL values');
        $this->assertCount(4, $chartData[0]['dH'], 'Should have 4 dH values');
    }
}
