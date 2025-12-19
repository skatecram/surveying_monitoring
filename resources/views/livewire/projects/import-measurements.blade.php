<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    <!-- Nullmessung -->
    <div class="border rounded-lg p-3 sm:p-4">
        <flux:heading size="lg" class="mb-4">Nullmessung</flux:heading>
        <form action="{{ route('measurements.import-null', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <flux:field>
                <flux:label>CSV-Datei importieren:</flux:label>
                <flux:input.file name="file" accept=".csv,.txt" required />
                <flux:description>Format: Punkt,E,N,H,Datum</flux:description>
            </flux:field>
            <flux:button type="submit" variant="primary" class="w-full">
                Importieren
            </flux:button>
        </form>

        @if($project->nullMeasurements->count() > 0)
            <div class="mt-4">
                <flux:subheading class="mb-2">Aktuelle Nullmessungen ({{ $project->nullMeasurements->count() }} Punkte):</flux:subheading>
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
        <flux:heading size="lg" class="mb-4">Kontrollmessungen</flux:heading>
        <form action="{{ route('measurements.import-control', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <flux:field>
                <flux:label>CSV-Datei importieren:</flux:label>
                <flux:input.file name="file" accept=".csv,.txt" required />
                <flux:description>Format: Punkt,E,N,H,Datum</flux:description>
            </flux:field>
            <flux:button type="submit" variant="primary" class="w-full">
                Importieren
            </flux:button>
        </form>

        @if($project->controlMeasurements->count() > 0)
            <div class="mt-4">
                <flux:subheading class="mb-2">Aktuelle Kontrollmessungen ({{ $project->controlMeasurements->count() }} Messungen):</flux:subheading>
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
