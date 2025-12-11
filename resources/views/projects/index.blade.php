@extends('layouts.app')

@section('title', 'Projekte')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h2 class="text-xl sm:text-2xl font-bold">Projekte</h2>
        <a href="{{ route('projects.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-center">
            Neues Projekt
        </a>
    </div>

    @if($projects->count() > 0)
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full min-w-[640px]">
                <thead class="bg-blue-900 text-white">
                    <tr>
                        <th class="px-2 sm:px-4 py-2 text-left text-sm">Projektnummer</th>
                        <th class="px-2 sm:px-4 py-2 text-left text-sm">Auftrag (Projektname)</th>
                        <th class="px-2 sm:px-4 py-2 text-left text-sm">Bearbeiter</th>
                        <th class="px-2 sm:px-4 py-2 text-left text-sm">Erstellt</th>
                        <th class="px-2 sm:px-4 py-2 text-center text-sm">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-2 sm:px-4 py-2 text-sm">{{ $project->number }}</td>
                        <td class="px-2 sm:px-4 py-2 text-sm">{{ $project->name }}</td>
                        <td class="px-2 sm:px-4 py-2 text-sm">{{ $project->bearbeiter }}</td>
                        <td class="px-2 sm:px-4 py-2 text-sm whitespace-nowrap">{{ $project->created_at->format('d.m.Y') }}</td>
                        <td class="px-2 sm:px-4 py-2 text-center">
                            <div class="flex flex-col sm:flex-row sm:justify-center gap-2 sm:gap-0 text-sm">
                                <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:underline sm:mr-3">Ansehen</a>
                                <a href="{{ route('projects.edit', $project) }}" class="text-yellow-600 hover:underline sm:mr-3">Bearbeiten</a>
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Projekt wirklich löschen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Löschen</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-600">Keine Projekte vorhanden. Erstellen Sie ein neues Projekt.</p>
    @endif
</div>
@endsection
