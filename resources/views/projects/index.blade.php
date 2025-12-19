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
            <table class="w-full min-w-[640px] border-collapse">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="px-2 sm:px-4 py-3 text-left text-sm font-semibold text-zinc-900 dark:text-white">Projektnummer</th>
                        <th class="px-2 sm:px-4 py-3 text-left text-sm font-semibold text-zinc-900 dark:text-white">Auftrag (Projektname)</th>
                        <th class="px-2 sm:px-4 py-3 text-left text-sm font-semibold text-zinc-900 dark:text-white">Bearbeiter</th>
                        <th class="px-2 sm:px-4 py-3 text-left text-sm font-semibold text-zinc-900 dark:text-white">Erstellt</th>
                        <th class="px-2 sm:px-4 py-3 text-center text-sm font-semibold text-zinc-900 dark:text-white">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($projects as $project)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-2 sm:px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $project->number }}</td>
                        <td class="px-2 sm:px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $project->name }}</td>
                        <td class="px-2 sm:px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $project->bearbeiter }}</td>
                        <td class="px-2 sm:px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400 whitespace-nowrap">{{ $project->created_at->format('d.m.Y') }}</td>
                        <td class="px-2 sm:px-4 py-3 text-center">
                            <div class="flex flex-col sm:flex-row sm:justify-center gap-2 sm:gap-0 text-sm">
                                <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 sm:mr-3">Ansehen</a>
                                <a href="{{ route('projects.edit', $project) }}" class="text-yellow-600 hover:text-yellow-700 dark:text-yellow-400 dark:hover:text-yellow-300 sm:mr-3">Bearbeiten</a>
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Projekt wirklich löschen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Löschen</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-zinc-600 dark:text-zinc-400">Keine Projekte vorhanden. Erstellen Sie ein neues Projekt.</p>
    @endif
</div>
@endsection
