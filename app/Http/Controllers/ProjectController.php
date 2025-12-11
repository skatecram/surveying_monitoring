<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::orderBy('created_at', 'desc')->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'bearbeiter' => 'required|string|max:255',
        ]);

        $project = Project::create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projekt erfolgreich erstellt!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load('nullMeasurements', 'controlMeasurements');
        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'bearbeiter' => 'required|string|max:255',
            'threshold_warning' => 'required|numeric|min:0',
            'threshold_caution' => 'required|numeric|min:0',
            'threshold_alarm' => 'required|numeric|min:0',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projekt erfolgreich aktualisiert!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Projekt erfolgreich gelöscht!');
    }
}
