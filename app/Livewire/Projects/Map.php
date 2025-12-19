<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class Map extends Component
{
    public Project $project;

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->project->load('nullMeasurements');
    }

    public function render()
    {
        return view('livewire.projects.map');
    }
}
