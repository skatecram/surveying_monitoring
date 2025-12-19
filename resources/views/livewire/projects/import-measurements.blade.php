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
