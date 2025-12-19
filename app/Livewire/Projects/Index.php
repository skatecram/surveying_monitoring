<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class Index extends Component
{
    public function delete($projectId)
    {
        $project = Project::findOrFail($projectId);
        $project->delete();
        
        session()->flash('success', 'Projekt erfolgreich gelöscht!');
    }

    public function render()
    {
        return view('livewire.projects.index', [
            'projects' => Project::orderBy('created_at', 'desc')->get(),
        ])->layout('layouts.app', ['title' => 'Projekte']);
    }
}
