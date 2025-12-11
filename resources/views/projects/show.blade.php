@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold">{{ $project->name }}</h2>
            <p class="text-gray-600">
                Projektnummer: {{ $project->number }} | Bearbeiter: {{ $project->bearbeiter }}
            </p>
        </div>
        <div class="space-x-2">
            <a href="{{ route('projects.edit', $project) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded">
                Bearbeiten
            </a>
            <a href="{{ route('projects.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Zurück
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button onclick="showTab('import')" id="tab-import" class="tab-button border-b-2 border-blue-600 py-4 px-1 font-medium text-blue-600">
                    Import
                </button>
                <button onclick="showTab('table')" id="tab-table" class="tab-button border-b-2 border-transparent py-4 px-1 font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Tabelle
                </button>
                <button onclick="showTab('diagrams')" id="tab-diagrams" class="tab-button border-b-2 border-transparent py-4 px-1 font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Diagramme
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div id="content-import" class="tab-content">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nullmessung -->
            <div class="border rounded-lg p-4">
                <h3 class="text-xl font-bold mb-4">Nullmessung</h3>
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
                        <h4 class="font-bold mb-2">Aktuelle Nullmessungen ({{ $project->nullMeasurements->count() }} Punkte):</h4>
                        <div class="max-h-64 overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="px-2 py-1">Punkt</th>
                                        <th class="px-2 py-1">E</th>
                                        <th class="px-2 py-1">N</th>
                                        <th class="px-2 py-1">H</th>
                                        <th class="px-2 py-1">Datum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->nullMeasurements as $measurement)
                                    <tr class="border-b">
                                        <td class="px-2 py-1">{{ $measurement->punkt }}</td>
                                        <td class="px-2 py-1">{{ number_format($measurement->E, 3) }}</td>
                                        <td class="px-2 py-1">{{ number_format($measurement->N, 3) }}</td>
                                        <td class="px-2 py-1">{{ number_format($measurement->H, 3) }}</td>
                                        <td class="px-2 py-1">{{ $measurement->date->format('d.m.Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Kontrollmessung -->
            <div class="border rounded-lg p-4">
                <h3 class="text-xl font-bold mb-4">Kontrollmessungen</h3>
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
                        <h4 class="font-bold mb-2">Aktuelle Kontrollmessungen ({{ $project->controlMeasurements->count() }} Messungen):</h4>
                        <div class="max-h-64 overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="px-2 py-1">Punkt</th>
                                        <th class="px-2 py-1">E</th>
                                        <th class="px-2 py-1">N</th>
                                        <th class="px-2 py-1">H</th>
                                        <th class="px-2 py-1">Datum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->controlMeasurements->sortBy('date') as $measurement)
                                    <tr class="border-b">
                                        <td class="px-2 py-1">{{ $measurement->punkt }}</td>
                                        <td class="px-2 py-1">{{ number_format($measurement->E, 3) }}</td>
                                        <td class="px-2 py-1">{{ number_format($measurement->N, 3) }}</td>
                                        <td class="px-2 py-1">{{ number_format($measurement->H, 3) }}</td>
                                        <td class="px-2 py-1">{{ $measurement->date->format('d.m.Y') }}</td>
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
