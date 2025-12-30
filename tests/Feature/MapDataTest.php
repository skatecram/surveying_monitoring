<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\NullMeasurement;
use App\Models\ControlMeasurement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_data_includes_null_and_latest_control_measurements(): void
    {
        // Create a test project
        $project = Project::create([
            'number' => 'TEST-001',
            'name' => 'Test Project',
            'bearbeiter' => 'Test User',
            'threshold_warning' => 3.0,
            'threshold_caution' => 5.0,
            'threshold_alarm' => 10.0,
        ]);

        // Create null measurements for two points
        NullMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P1',
            'E' => 2600000.123,
            'N' => 1200000.456,
            'H' => 450.123,
            'date' => now()->subDays(30),
        ]);

        NullMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P2',
            'E' => 2600010.234,
            'N' => 1200005.567,
            'H' => 451.234,
            'date' => now()->subDays(30),
        ]);

        // Create multiple control measurements for P1 (only the latest should be shown)
        ControlMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P1',
            'E' => 2600000.125,
            'N' => 1200000.458,
            'H' => 450.125,
            'date' => now()->subDays(15),
        ]);

        ControlMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P1',
            'E' => 2600000.127,
            'N' => 1200000.460,
            'H' => 450.127,
            'date' => now()->subDays(5), // This is the latest
        ]);

        // Create one control measurement for P2
        ControlMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P2',
            'E' => 2600010.236,
            'N' => 1200005.569,
            'H' => 451.236,
            'date' => now()->subDays(10),
        ]);

        // Request map data
        $response = $this->get(route('projects.map-data', $project));

        $response->assertStatus(200);
        $data = $response->json();

        // Verify structure
        $this->assertArrayHasKey('nullMeasurements', $data);
        $this->assertArrayHasKey('controlMeasurements', $data);

        // Verify null measurements
        $this->assertCount(2, $data['nullMeasurements']);
        
        // Verify control measurements - should only have 2 (latest for each point)
        $this->assertCount(2, $data['controlMeasurements']);

        // Verify P1 control measurement is the latest one
        $p1Control = collect($data['controlMeasurements'])->firstWhere('punkt', 'P1');
        $this->assertNotNull($p1Control);
        $this->assertEquals(2600000.127, $p1Control['E']);
        $this->assertEquals(1200000.460, $p1Control['N']);
        $this->assertEquals(450.127, $p1Control['H']);
        $this->assertEquals('control', $p1Control['type']);

        // Verify null measurements have correct type
        $p1Null = collect($data['nullMeasurements'])->firstWhere('punkt', 'P1');
        $this->assertNotNull($p1Null);
        $this->assertEquals('null', $p1Null['type']);

        // Verify coordinates are converted
        $this->assertArrayHasKey('lat', $p1Null);
        $this->assertArrayHasKey('lng', $p1Null);
        $this->assertIsNumeric($p1Null['lat']);
        $this->assertIsNumeric($p1Null['lng']);
    }

    public function test_map_data_without_control_measurements(): void
    {
        // Create a test project
        $project = Project::create([
            'number' => 'TEST-002',
            'name' => 'Test Project 2',
            'bearbeiter' => 'Test User',
            'threshold_warning' => 3.0,
            'threshold_caution' => 5.0,
            'threshold_alarm' => 10.0,
        ]);

        // Create only null measurements
        NullMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P1',
            'E' => 2600000.123,
            'N' => 1200000.456,
            'H' => 450.123,
            'date' => now()->subDays(30),
        ]);

        // Request map data
        $response = $this->get(route('projects.map-data', $project));

        $response->assertStatus(200);
        $data = $response->json();

        // Verify structure
        $this->assertArrayHasKey('nullMeasurements', $data);
        $this->assertArrayHasKey('controlMeasurements', $data);

        // Verify null measurements exist
        $this->assertCount(1, $data['nullMeasurements']);
        
        // Verify no control measurements
        $this->assertCount(0, $data['controlMeasurements']);
    }
}
