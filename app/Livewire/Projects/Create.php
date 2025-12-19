<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class Create extends Component
{
    public $number = '';
    public $name = '';
    public $bearbeiter = '';

    protected $rules = [
        'number' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'bearbeiter' => 'required|string|max:255',
    ];

    public function save()
    {
        $validated = $this->validate();

        $project = Project::create($validated);

        session()->flash('success', 'Projekt erfolgreich erstellt!');

        return redirect()->route('projects.show', $project);
    }

    public function render()
    {
        return view('livewire.projects.create')
            ->layout('layouts.app', ['title' => 'Neues Projekt']);
    }
}
