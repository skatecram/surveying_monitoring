<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class Edit extends Component
{
    public Project $project;
    public $number;
    public $name;
    public $bearbeiter;
    public $threshold_warning;
    public $threshold_caution;
    public $threshold_alarm;

    protected $rules = [
        'number' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'bearbeiter' => 'required|string|max:255',
        'threshold_warning' => 'required|numeric|min:0',
        'threshold_caution' => 'required|numeric|min:0',
        'threshold_alarm' => 'required|numeric|min:0',
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->number = $project->number;
        $this->name = $project->name;
        $this->bearbeiter = $project->bearbeiter;
        $this->threshold_warning = $project->threshold_warning;
        $this->threshold_caution = $project->threshold_caution;
        $this->threshold_alarm = $project->threshold_alarm;
    }

    public function save()
    {
        $validated = $this->validate();

        $this->project->update($validated);

        session()->flash('success', 'Projekt erfolgreich aktualisiert!');

        return redirect()->route('projects.show', $this->project);
    }

    public function render()
    {
        return view('livewire.projects.edit')
            ->layout('layouts.app', ['title' => 'Projekt bearbeiten']);
    }
}
