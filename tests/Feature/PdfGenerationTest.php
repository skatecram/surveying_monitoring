<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\NullMeasurement;
use App\Models\ControlMeasurement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_generation_with_qr_code_and_charts(): void
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

        // Create null measurements
        NullMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P1',
            'E' => 2500000.123,
            'N' => 1200000.456,
            'H' => 450.123,
            'date' => now()->subDays(30),
        ]);

        // Create control measurements
        ControlMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P1',
            'E' => 2500000.125,
            'N' => 1200000.458,
            'H' => 450.125,
            'date' => now()->subDays(15),
        ]);

        ControlMeasurement::create([
            'project_id' => $project->id,
            'punkt' => 'P1',
            'E' => 2500000.127,
            'N' => 1200000.460,
            'H' => 450.127,
            'date' => now(),
        ]);

        // Generate PDF
        $response = $this->get(route('reports.pdf', $project));

        // Check that the PDF was generated successfully
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_generation_without_data(): void
    {
        // Create a test project without measurements
        $project = Project::create([
            'number' => 'TEST-002',
            'name' => 'Empty Project',
            'bearbeiter' => 'Test User',
            'threshold_warning' => 3.0,
            'threshold_caution' => 5.0,
            'threshold_alarm' => 10.0,
        ]);

        // Generate PDF
        $response = $this->get(route('reports.pdf', $project));

        // Check that the PDF was generated successfully even without data
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
