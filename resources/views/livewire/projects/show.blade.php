<flux:container>
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-6">
        <div class="flex-1">
            <flux:heading size="xl" class="break-words">{{ $project->name }}</flux:heading>
            <flux:text class="text-sm sm:text-base break-words">
                Projektnummer: {{ $project->number }} | Bearbeiter: {{ $project->bearbeiter }}
            </flux:text>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <flux:button href="{{ route('projects.edit', $project) }}" class="bg-yellow-600 hover:bg-yellow-700">
                Bearbeiten
            </flux:button>
            <flux:button href="{{ route('projects.index') }}" variant="ghost">
                Zurück
            </flux:button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="-mb-px flex space-x-4 sm:space-x-8 min-w-min">
                <flux:button 
                    wire:click="setTab('import')" 
                    variant="ghost"
                    class="border-b-2 py-3 sm:py-4 px-1 font-medium text-sm sm:text-base whitespace-nowrap {{ $activeTab === 'import' ? 'border-blue-600 text-blue-600' : 'border-transparent' }}">
                    Import
                </flux:button>
                <flux:button 
                    wire:click="setTab('table')" 
                    variant="ghost"
                    class="border-b-2 py-3 sm:py-4 px-1 font-medium text-sm sm:text-base whitespace-nowrap {{ $activeTab === 'table' ? 'border-blue-600 text-blue-600' : 'border-transparent' }}">
                    Tabelle
                </flux:button>
                <flux:button 
                    wire:click="setTab('diagrams')" 
                    variant="ghost"
                    class="border-b-2 py-3 sm:py-4 px-1 font-medium text-sm sm:text-base whitespace-nowrap {{ $activeTab === 'diagrams' ? 'border-blue-600 text-blue-600' : 'border-transparent' }}">
                    Diagramme
                </flux:button>
                <flux:button 
                    wire:click="setTab('map')" 
                    variant="ghost"
                    class="border-b-2 py-3 sm:py-4 px-1 font-medium text-sm sm:text-base whitespace-nowrap {{ $activeTab === 'map' ? 'border-blue-600 text-blue-600' : 'border-transparent' }}">
                    Karte
                </flux:button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    @if($activeTab === 'import')
        <livewire:projects.import-measurements :project="$project" :key="'import-'.$project->id" />
    @elseif($activeTab === 'table')
        <livewire:projects.deviation-table :project="$project" :key="'table-'.$project->id" />
    @elseif($activeTab === 'diagrams')
        <livewire:projects.diagrams :project="$project" :key="'diagrams-'.$project->id" />
    @elseif($activeTab === 'map')
        <livewire:projects.map :project="$project" :key="'map-'.$project->id" />
    @endif
</flux:container>
