<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function generatePdf(Project $project)
    {
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
        
        $pdf = Pdf::loadView('reports.pdf', [
            'project' => $project,
            'controlByPoint' => $controlByPoint,
            'nullByPoint' => $nullByPoint,
            'chartData' => $chartData,
        ])->setPaper('a4', 'portrait');
        
        $filename = 'Bericht_' . $project->number . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}
