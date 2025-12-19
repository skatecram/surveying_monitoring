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
            <flux:button type="submit" variant="primary" class="w-full">
                Importieren
            </flux:button>
        </form>

        @if($project->nullMeasurements->count() > 0)
            <div class="mt-4">
                <h4 class="font-bold mb-2 text-sm sm:text-base">Aktuelle Nullmessungen ({{ $project->nullMeasurements->count() }} Punkte):</h4>
                <div class="max-h-64 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm min-w-[400px]">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-1 sm:px-2 py-1">Punkt</th>
                                <th class="px-1 sm:px-2 py-1">E</th>
                                <th class="px-1 sm:px-2 py-1">N</th>
                                <th class="px-1 sm:px-2 py-1">H</th>
                                <th class="px-1 sm:px-2 py-1">Datum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->nullMeasurements as $measurement)
                            <tr class="border-b">
                                <td class="px-1 sm:px-2 py-1">{{ $measurement->punkt }}</td>
                                <td class="px-1 sm:px-2 py-1">{{ number_format($measurement->E, 3) }}</td>
                                <td class="px-1 sm:px-2 py-1">{{ number_format($measurement->N, 3) }}</td>
                                <td class="px-1 sm:px-2 py-1">{{ number_format($measurement->H, 3) }}</td>
                                <td class="px-1 sm:px-2 py-1 whitespace-nowrap">{{ $measurement->date->format('d.m.Y') }}</td>
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
            <flux:button type="submit" variant="primary" class="w-full">
                Importieren
            </flux:button>
        </form>

        @if($project->controlMeasurements->count() > 0)
            <div class="mt-4">
                <h4 class="font-bold mb-2 text-sm sm:text-base">Aktuelle Kontrollmessungen ({{ $project->controlMeasurements->count() }} Messungen):</h4>
                <div class="max-h-64 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm min-w-[400px]">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-1 sm:px-2 py-1">Punkt</th>
                                <th class="px-1 sm:px-2 py-1">E</th>
                                <th class="px-1 sm:px-2 py-1">N</th>
                                <th class="px-1 sm:px-2 py-1">H</th>
                                <th class="px-1 sm:px-2 py-1">Datum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->controlMeasurements->sortBy('date') as $measurement)
                            <tr class="border-b">
                                <td class="px-1 sm:px-2 py-1">{{ $measurement->punkt }}</td>
                                <td class="px-1 sm:px-2 py-1">{{ number_format($measurement->E, 3) }}</td>
                                <td class="px-1 sm:px-2 py-1">{{ number_format($measurement->N, 3) }}</td>
                                <td class="px-1 sm:px-2 py-1">{{ number_format($measurement->H, 3) }}</td>
                                <td class="px-1 sm:px-2 py-1 whitespace-nowrap">{{ $measurement->date->format('d.m.Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
