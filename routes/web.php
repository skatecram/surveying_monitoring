<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('projects.index');
});

Route::resource('projects', ProjectController::class);

Route::post('projects/{project}/measurements/import-null', [MeasurementController::class, 'importNull'])
    ->name('measurements.import-null');

Route::post('projects/{project}/measurements/import-control', [MeasurementController::class, 'importControl'])
    ->name('measurements.import-control');

Route::get('projects/{project}/report/pdf', [ReportController::class, 'generatePdf'])
    ->name('reports.pdf');
