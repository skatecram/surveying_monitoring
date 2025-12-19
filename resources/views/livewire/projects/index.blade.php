<div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h2 class="text-xl sm:text-2xl font-bold">Projekte</h2>
        <flux:button href="{{ route('projects.create') }}" variant="primary">
            Neues Projekt
        </flux:button>
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
                            <div class="flex flex-col sm:flex-row sm:justify-center gap-2 sm:gap-2 text-sm">
                                <flux:button href="{{ route('projects.show', $project) }}" size="sm" variant="ghost">Ansehen</flux:button>
                                <flux:button href="{{ route('projects.edit', $project) }}" size="sm" variant="ghost">Bearbeiten</flux:button>
                                <flux:button wire:click="delete({{ $project->id }})" wire:confirm="Projekt wirklich löschen?" size="sm" variant="ghost">Löschen</flux:button>
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
