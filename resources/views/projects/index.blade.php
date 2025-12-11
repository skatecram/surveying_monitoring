@extends('layouts.app')

@section('title', 'Projekte')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Projekte</h2>
        <a href="{{ route('projects.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Neues Projekt
        </a>
    </div>

    @if($projects->count() > 0)
        <table class="w-full">
            <thead class="bg-blue-900 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Projektnummer</th>
                    <th class="px-4 py-2 text-left">Auftrag (Projektname)</th>
                    <th class="px-4 py-2 text-left">Bearbeiter</th>
                    <th class="px-4 py-2 text-left">Erstellt</th>
                    <th class="px-4 py-2 text-center">Aktionen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $project->number }}</td>
                    <td class="px-4 py-2">{{ $project->name }}</td>
                    <td class="px-4 py-2">{{ $project->bearbeiter }}</td>
                    <td class="px-4 py-2">{{ $project->created_at->format('d.m.Y') }}</td>
                    <td class="px-4 py-2 text-center">
                        <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:underline mr-3">Ansehen</a>
                        <a href="{{ route('projects.edit', $project) }}" class="text-yellow-600 hover:underline mr-3">Bearbeiten</a>
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirm('Projekt wirklich löschen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Löschen</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-600">Keine Projekte vorhanden. Erstellen Sie ein neues Projekt.</p>
    @endif
</div>
@endsection
