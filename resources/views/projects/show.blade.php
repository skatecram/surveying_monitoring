@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-6">
        <div class="flex-1">
            <h2 class="text-xl sm:text-2xl font-bold break-words">{{ $project->name }}</h2>
            <p class="text-sm sm:text-base text-gray-600 break-words">
                Projektnummer: {{ $project->number }} | Bearbeiter: {{ $project->bearbeiter }}
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <a href="{{ route('projects.edit', $project) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded text-center">
                Bearbeiten
            </a>
            <a href="{{ route('projects.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-center">
                Zurück
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="-mb-px flex space-x-4 sm:space-x-8 min-w-min">
                <button onclick="showTab('import')" id="tab-import" class="tab-button border-b-2 border-blue-600 py-3 sm:py-4 px-1 font-medium text-sm sm:text-base text-blue-600 whitespace-nowrap">
                    Import
                </button>
                <button onclick="showTab('table')" id="tab-table" class="tab-button border-b-2 border-transparent py-3 sm:py-4 px-1 font-medium text-sm sm:text-base text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    Tabelle
                </button>
                <button onclick="showTab('diagrams')" id="tab-diagrams" class="tab-button border-b-2 border-transparent py-3 sm:py-4 px-1 font-medium text-sm sm:text-base text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    Diagramme
                </button>
                <button onclick="showTab('map')" id="tab-map" class="tab-button border-b-2 border-transparent py-3 sm:py-4 px-1 font-medium text-sm sm:text-base text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    Karte
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div id="content-import" class="tab-content">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Nullmessung -->
            <div class="border rounded-lg p-3 sm:p-4">
                <h3 class="text-lg sm:text-xl font-bold mb-4">Nullmessung</h3>
                <form action="{{ route('measurements.import-null', $project) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">CSV-Datei importieren:</label>
                        <input type="file" name="file" accept=".csv,.txt" class="w-full px-3 py-2 border rounded" required>
                        <p class="text-sm text-gray-600 mt-2">Format: Punkt,E,N,H,Datum</p>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">
                        Importieren
                    </button>
                </form>

                @if($project->nullMeasurements->count() > 0)
                    <div class="mt-4">
                        <h4 class="font-bold mb-2 text-sm sm:text-base">Aktuelle Nullmessungen ({{ $project->nullMeasurements->count() }} Punkte):</h4>
                        <div class="max-h-64 overflow-y-auto overflow-x-auto">
                            <table class="w-full text-xs sm:text-sm min-w-[400px] border-collapse">
                                <thead>
                                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                        <th class="px-1 sm:px-2 py-2 text-left font-semibold text-zinc-900 dark:text-white">Punkt</th>
                                        <th class="px-1 sm:px-2 py-2 text-left font-semibold text-zinc-900 dark:text-white">E</th>
                                        <th class="px-1 sm:px-2 py-2 text-left font-semibold text-zinc-900 dark:text-white">N</th>
                                        <th class="px-1 sm:px-2 py-2 text-left font-semibold text-zinc-900 dark:text-white">H</th>
                                        <th class="px-1 sm:px-2 py-2 text-left font-semibold text-zinc-900 dark:text-white">Datum</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($project->nullMeasurements as $measurement)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-1 sm:px-2 py-1 text-zinc-900 dark:text-zinc-100">{{ $measurement->punkt }}</td>
                                        <td class="px-1 sm:px-2 py-1 text-zinc-600 dark:text-zinc-400">{{ number_format($measurement->E, 3) }}</td>
                                        <td class="px-1 sm:px-2 py-1 text-zinc-600 dark:text-zinc-400">{{ number_format($measurement->N, 3) }}</td>
                                        <td class="px-1 sm:px-2 py-1 text-zinc-600 dark:text-zinc-400">{{ number_format($measurement->H, 3) }}</td>
                                        <td class="px-1 sm:px-2 py-1 whitespace-nowrap text-zinc-600 dark:text-zinc-400">{{ $measurement->date->format('d.m.Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Kontrollmessung -->
            <div class="border rounded-lg p-3 sm:p-4">
                <h3 class="text-lg sm:text-xl font-bold mb-4">Kontrollmessungen</h3>
                <form action="{{ route('measurements.import-control', $project) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">CSV-Datei importieren:</label>
                        <input type="file" name="file" accept=".csv,.txt" class="w-full px-3 py-2 border rounded" required>
                        <p class="text-sm text-gray-600 mt-2">Format: Punkt,E,N,H,Datum</p>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">
                        Importieren
                    </button>
                </form>

                @if($project->controlMeasurements->count() > 0)
                    <div class="mt-4">
                        <h4 class="font-bold mb-2 text-sm sm:text-base">Aktuelle Kontrollmessungen ({{ $project->controlMeasurements->count() }} Messungen):</h4>
                        <div class="max-h-64 overflow-y-auto overflow-x-auto">
                            <table class="w-full text-xs sm:text-sm min-w-[400px] border-collapse">
                                <thead>
                                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                        <th class="px-1 sm:px-2 py-2 text-left font-semibold text-zinc-900 dark:text-white">Punkt</th>
                                        <th class="px-1 sm:px-2 py-2 text-left font-semibold text-zinc-900 dark:text-white">E</th>
                                        <th class="px-1 sm:px-2 py-2 text-left font-semibold text-zinc-900 dark:text-white">N</th>
                                        <th class="px-1 sm:px-2 py-2 text-left font-semibold text-zinc-900 dark:text-white">H</th>
                                        <th class="px-1 sm:px-2 py-2 text-left font-semibold text-zinc-900 dark:text-white">Datum</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach($project->controlMeasurements->sortBy('date') as $measurement)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-1 sm:px-2 py-1 text-zinc-900 dark:text-zinc-100">{{ $measurement->punkt }}</td>
                                        <td class="px-1 sm:px-2 py-1 text-zinc-600 dark:text-zinc-400">{{ number_format($measurement->E, 3) }}</td>
                                        <td class="px-1 sm:px-2 py-1 text-zinc-600 dark:text-zinc-400">{{ number_format($measurement->N, 3) }}</td>
                                        <td class="px-1 sm:px-2 py-1 text-zinc-600 dark:text-zinc-400">{{ number_format($measurement->H, 3) }}</td>
                                        <td class="px-1 sm:px-2 py-1 whitespace-nowrap text-zinc-600 dark:text-zinc-400">{{ $measurement->date->format('d.m.Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="content-table" class="tab-content hidden">
        @include('projects.partials.deviation-table')
    </div>

    <div id="content-diagrams" class="tab-content hidden">
        @include('projects.partials.diagrams')
    </div>

    <div id="content-map" class="tab-content hidden">
        @include('projects.partials.map')
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-600', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-600', 'text-blue-600');
}
</script>
@endsection
