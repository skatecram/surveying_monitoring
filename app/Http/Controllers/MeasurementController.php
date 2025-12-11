<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\NullMeasurement;
use App\Models\ControlMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeasurementController extends Controller
{
    public function importNull(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $file = $request->file('file');
            $content = file_get_contents($file->getRealPath());
            
            // Try different encodings
            $encodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252'];
            foreach ($encodings as $encoding) {
                if (mb_check_encoding($content, $encoding)) {
                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                    break;
                }
            }
            
            $lines = explode("\n", $content);
            $imported = 0;
            
            DB::beginTransaction();
            
            // Delete existing null measurements for this project
            $project->nullMeasurements()->delete();
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Try different delimiters
                $delimiter = strpos($line, ';') !== false ? ';' : ',';
                $parts = str_getcsv($line, $delimiter);
                
                if (count($parts) >= 5) {
                    $punkt = trim($parts[0]);
                    $E = floatval(str_replace(',', '.', $parts[1]));
                    $N = floatval(str_replace(',', '.', $parts[2]));
                    $H = floatval(str_replace(',', '.', $parts[3]));
                    $dateStr = trim($parts[4]);
                    
                    // Parse date (support various formats)
                    $date = $this->parseDate($dateStr);
                    
                    if ($date) {
                        NullMeasurement::create([
                            'project_id' => $project->id,
                            'punkt' => $punkt,
                            'E' => $E,
                            'N' => $N,
                            'H' => $H,
                            'date' => $date,
                        ]);
                        $imported++;
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('projects.show', $project)
                ->with('success', "Nullmessungen erfolgreich importiert! ($imported Punkte)");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('projects.show', $project)
                ->with('error', 'Fehler beim Import: ' . $e->getMessage());
        }
    }

    public function importControl(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $file = $request->file('file');
            $content = file_get_contents($file->getRealPath());
            
            // Try different encodings
            $encodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252'];
            foreach ($encodings as $encoding) {
                if (mb_check_encoding($content, $encoding)) {
                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                    break;
                }
            }
            
            $lines = explode("\n", $content);
            $imported = 0;
            
            DB::beginTransaction();
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Try different delimiters
                $delimiter = strpos($line, ';') !== false ? ';' : ',';
                $parts = str_getcsv($line, $delimiter);
                
                if (count($parts) >= 5) {
                    $punkt = trim($parts[0]);
                    $E = floatval(str_replace(',', '.', $parts[1]));
                    $N = floatval(str_replace(',', '.', $parts[2]));
                    $H = floatval(str_replace(',', '.', $parts[3]));
                    $dateStr = trim($parts[4]);
                    
                    // Parse date
                    $date = $this->parseDate($dateStr);
                    
                    if ($date) {
                        ControlMeasurement::create([
                            'project_id' => $project->id,
                            'punkt' => $punkt,
                            'E' => $E,
                            'N' => $N,
                            'H' => $H,
                            'date' => $date,
                        ]);
                        $imported++;
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('projects.show', $project)
                ->with('success', "Kontrollmessungen erfolgreich importiert! ($imported Messungen)");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('projects.show', $project)
                ->with('error', 'Fehler beim Import: ' . $e->getMessage());
        }
    }

    private function parseDate($dateStr)
    {
        // Try various date formats
        $formats = [
            'Y-m-d',
            'd.m.Y',
            'd/m/Y',
            'm/d/Y',
            'Y/m/d',
            'd-m-Y',
        ];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateStr);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }
        
        return null;
    }
}
