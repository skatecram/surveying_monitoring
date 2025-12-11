@extends('layouts.app')

@section('title', 'Projekt bearbeiten')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 max-w-2xl mx-auto">
    <h2 class="text-xl sm:text-2xl font-bold mb-6">Projekt bearbeiten</h2>

    <form action="{{ route('projects.update', $project) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <label for="number" class="block text-gray-700 font-bold mb-2">Auftragsnummer:</label>
            <input type="text" name="number" id="number" value="{{ old('number', $project->number) }}" 
                   class="w-full px-3 py-2 border rounded @error('number') border-red-500 @enderror" required>
            @error('number')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Auftrag (Projektname):</label>
            <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" 
                   class="w-full px-3 py-2 border rounded @error('name') border-red-500 @enderror" required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="bearbeiter" class="block text-gray-700 font-bold mb-2">Bearbeiter:</label>
            <input type="text" name="bearbeiter" id="bearbeiter" value="{{ old('bearbeiter', $project->bearbeiter) }}" 
                   class="w-full px-3 py-2 border rounded @error('bearbeiter') border-red-500 @enderror" required>
            @error('bearbeiter')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="threshold_warning" class="block text-gray-700 font-bold mb-2">Aufmerksamkeitswert (mm):</label>
            <input type="number" step="0.01" name="threshold_warning" id="threshold_warning" 
                   value="{{ old('threshold_warning', $project->threshold_warning) }}" 
                   class="w-full px-3 py-2 border rounded @error('threshold_warning') border-red-500 @enderror" required>
            @error('threshold_warning')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="threshold_caution" class="block text-gray-700 font-bold mb-2">Interventionswert (mm):</label>
            <input type="number" step="0.01" name="threshold_caution" id="threshold_caution" 
                   value="{{ old('threshold_caution', $project->threshold_caution) }}" 
                   class="w-full px-3 py-2 border rounded @error('threshold_caution') border-red-500 @enderror" required>
            @error('threshold_caution')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="threshold_alarm" class="block text-gray-700 font-bold mb-2">Alarmwert (mm):</label>
            <input type="number" step="0.01" name="threshold_alarm" id="threshold_alarm" 
                   value="{{ old('threshold_alarm', $project->threshold_alarm) }}" 
                   class="w-full px-3 py-2 border rounded @error('threshold_alarm') border-red-500 @enderror" required>
            @error('threshold_alarm')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col sm:flex-row sm:justify-between gap-2">
            <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-center">
                Abbrechen
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Aktualisieren
            </button>
        </div>
    </form>
</div>
@endsection
