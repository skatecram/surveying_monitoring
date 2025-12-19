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
                <button 
                    wire:click="setTab('import')" 
                    class="border-b-2 py-3 sm:py-4 px-1 font-medium text-sm sm:text-base whitespace-nowrap
                           {{ $activeTab === 'import' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Import
                </button>
                <button 
                    wire:click="setTab('table')" 
                    class="border-b-2 py-3 sm:py-4 px-1 font-medium text-sm sm:text-base whitespace-nowrap
                           {{ $activeTab === 'table' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Tabelle
                </button>
                <button 
                    wire:click="setTab('diagrams')" 
                    class="border-b-2 py-3 sm:py-4 px-1 font-medium text-sm sm:text-base whitespace-nowrap
                           {{ $activeTab === 'diagrams' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Diagramme
                </button>
                <button 
                    wire:click="setTab('map')" 
                    class="border-b-2 py-3 sm:py-4 px-1 font-medium text-sm sm:text-base whitespace-nowrap
                           {{ $activeTab === 'map' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Karte
                </button>
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
</div>
