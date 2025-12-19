<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\ReportController;
use App\Livewire\Projects;

Route::get('/', function () {
    return redirect()->route('projects.index');
});

// Livewire routes for project management
Route::get('projects', Projects\Index::class)->name('projects.index');
Route::get('projects/create', Projects\Create::class)->name('projects.create');
Route::get('projects/{project}/edit', Projects\Edit::class)->name('projects.edit');
Route::get('projects/{project}', Projects\Show::class)->name('projects.show');

Route::get('projects/{project}/map-data', [ProjectController::class, 'mapData'])
    ->name('projects.map-data');

Route::post('projects/{project}/measurements/import-null', [MeasurementController::class, 'importNull'])
    ->name('measurements.import-null');

Route::post('projects/{project}/measurements/import-control', [MeasurementController::class, 'importControl'])
    ->name('measurements.import-control');

Route::get('projects/{project}/report/pdf', [ReportController::class, 'generatePdf'])
    ->name('reports.pdf');
