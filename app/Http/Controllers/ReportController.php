<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

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
        
        // Generate QR code for project URL
        $projectUrl = route('projects.show', $project);
        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->data($projectUrl)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(200)
            ->margin(10)
            ->build();
        
        $qrCodeDataUri = $qrCode->getDataUri();
        
        // Generate chart images using QuickChart API
        $chartImages = $this->generateChartImages($chartData);
        
        $pdf = Pdf::loadView('reports.pdf', [
            'project' => $project,
            'controlByPoint' => $controlByPoint,
            'nullByPoint' => $nullByPoint,
            'chartData' => $chartData,
            'qrCodeDataUri' => $qrCodeDataUri,
            'projectUrl' => $projectUrl,
            'chartImages' => $chartImages,
        ])->setPaper('a4', 'portrait');
        
        $filename = 'Bericht_' . $project->number . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    private function generateChartImages($chartData)
    {
        if (empty($chartData)) {
            return [
                'xyShift' => null,
                'twoDShift' => null,
                'hShift' => null,
                'xyVector' => null,
            ];
        }
        
        $colors = [
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
        
        // Chart 1: ΔE and ΔN over time
        $datasets1 = [];
        foreach ($chartData as $index => $point) {
            $datasets1[] = [
                'label' => $point['punkt'] . ' (ΔE)',
                'data' => array_map(fn($i) => ['x' => $point['dates'][$i], 'y' => $point['dE'][$i]], array_keys($point['dates'])),
                'borderColor' => $colors[$index % count($colors)],
                'borderDash' => [5, 5],
                'fill' => false,
            ];
            $datasets1[] = [
                'label' => $point['punkt'] . ' (ΔN)',
                'data' => array_map(fn($i) => ['x' => $point['dates'][$i], 'y' => $point['dN'][$i]], array_keys($point['dates'])),
                'borderColor' => $colors[$index % count($colors)],
                'fill' => false,
            ];
        }
        
        // Chart 2: 2D shift
        $datasets2 = [];
        foreach ($chartData as $index => $point) {
            $datasets2[] = [
                'label' => $point['punkt'] . ' (ΔL)',
                'data' => array_map(fn($i) => ['x' => $point['dates'][$i], 'y' => $point['dL'][$i]], array_keys($point['dates'])),
                'borderColor' => $colors[$index % count($colors)],
                'fill' => false,
            ];
        }
        
        // Chart 3: ΔH
        $datasets3 = [];
        foreach ($chartData as $index => $point) {
            $datasets3[] = [
                'label' => $point['punkt'] . ' (ΔH)',
                'data' => array_map(fn($i) => ['x' => $point['dates'][$i], 'y' => $point['dH'][$i]], array_keys($point['dates'])),
                'borderColor' => $colors[$index % count($colors)],
                'fill' => false,
            ];
        }
        
        // Chart 4: XY Vector (scatter plot showing displacement endpoints)
        $datasets4 = [];
        foreach ($chartData as $index => $point) {
            $displacementPoints = array_map(fn($i) => ['x' => $point['dE'][$i], 'y' => $point['dN'][$i]], array_keys($point['dE']));
            $datasets4[] = [
                'label' => $point['punkt'],
                'data' => $displacementPoints,
                'borderColor' => $colors[$index % count($colors)],
                'backgroundColor' => $colors[$index % count($colors)],
                'showLine' => true,
            ];
        }
        
        // Calculate axis range for Chart 4
        $maxAbsX = 0;
        $maxAbsY = 0;
        foreach ($chartData as $point) {
            foreach ($point['dE'] as $val) $maxAbsX = max($maxAbsX, abs($val));
            foreach ($point['dN'] as $val) $maxAbsY = max($maxAbsY, abs($val));
        }
        $maxAbs = max($maxAbsX, $maxAbsY);
        $axisRange = $maxAbs > 0 ? $maxAbs * 1.1 : 10;
        
        return [
            'xyShift' => $this->getQuickChartUrl([
                'type' => 'line',
                'data' => ['datasets' => $datasets1],
                'options' => [
                    'scales' => [
                        'x' => ['type' => 'time', 'time' => ['unit' => 'day'], 'title' => ['display' => true, 'text' => 'Datum']],
                        'y' => ['title' => ['display' => true, 'text' => 'Abweichung (mm)']],
                    ],
                    'plugins' => ['title' => ['display' => true, 'text' => 'ΔE und ΔN je Punkt über Zeit']],
                ],
            ]),
            'twoDShift' => $this->getQuickChartUrl([
                'type' => 'line',
                'data' => ['datasets' => $datasets2],
                'options' => [
                    'scales' => [
                        'x' => ['type' => 'time', 'time' => ['unit' => 'day'], 'title' => ['display' => true, 'text' => 'Datum']],
                        'y' => ['title' => ['display' => true, 'text' => '2D Verschiebung (mm)'], 'beginAtZero' => true],
                    ],
                    'plugins' => ['title' => ['display' => true, 'text' => '2D Lageverschiebung je Punkt über Zeit']],
                ],
            ]),
            'hShift' => $this->getQuickChartUrl([
                'type' => 'line',
                'data' => ['datasets' => $datasets3],
                'options' => [
                    'scales' => [
                        'x' => ['type' => 'time', 'time' => ['unit' => 'day'], 'title' => ['display' => true, 'text' => 'Datum']],
                        'y' => ['title' => ['display' => true, 'text' => 'Höhenabweichung (mm)']],
                    ],
                    'plugins' => ['title' => ['display' => true, 'text' => 'ΔH je Punkt über Zeit']],
                ],
            ]),
            'xyVector' => $this->getQuickChartUrl([
                'type' => 'scatter',
                'data' => ['datasets' => $datasets4],
                'options' => [
                    'scales' => [
                        'x' => ['title' => ['display' => true, 'text' => 'ΔE (mm)'], 'min' => -$axisRange, 'max' => $axisRange],
                        'y' => ['title' => ['display' => true, 'text' => 'ΔN (mm)'], 'min' => -$axisRange, 'max' => $axisRange],
                    ],
                    'plugins' => ['title' => ['display' => true, 'text' => 'Verschiebung als Vektoren (XY)']],
                ],
            ]),
        ];
    }
    
    private function getQuickChartUrl($chartConfig)
    {
        $baseUrl = 'https://quickchart.io/chart';
        $width = 600;
        $height = 300;
        
        $params = [
            'width' => $width,
            'height' => $height,
            'chart' => json_encode($chartConfig),
        ];
        
        return $baseUrl . '?' . http_build_query($params);
    }
}
