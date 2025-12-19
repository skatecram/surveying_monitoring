<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class Show extends Component
{
    public Project $project;
    public $activeTab = 'import';

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->project->load('nullMeasurements', 'controlMeasurements');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.projects.show')
            ->layout('layouts.app', ['title' => $this->project->name]);
    }
}
