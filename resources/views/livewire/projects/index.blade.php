<flux:container>
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <flux:heading size="xl">Projekte</flux:heading>
        <flux:button href="{{ route('projects.create') }}" variant="primary">
            Neues Projekt
        </flux:button>
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
                            <div class="flex flex-col sm:flex-row sm:justify-center gap-2 sm:gap-2">
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
        <flux:text>Keine Projekte vorhanden. Erstellen Sie ein neues Projekt.</flux:text>
    @endif
</flux:container>
