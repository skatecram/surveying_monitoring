<div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 max-w-2xl mx-auto">
    <h2 class="text-xl sm:text-2xl font-bold mb-6">Neues Projekt erstellen</h2>

    <form wire:submit="save">
        <div class="mb-4">
            <label for="number" class="block text-gray-700 font-bold mb-2">Auftragsnummer:</label>
            <input type="text" wire:model="number" id="number" 
                   class="w-full px-3 py-2 border rounded @error('number') border-red-500 @enderror" required>
            @error('number')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-bold mb-2">Auftrag (Projektname):</label>
            <input type="text" wire:model="name" id="name" 
                   class="w-full px-3 py-2 border rounded @error('name') border-red-500 @enderror" required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="bearbeiter" class="block text-gray-700 font-bold mb-2">Bearbeiter:</label>
            <input type="text" wire:model="bearbeiter" id="bearbeiter" 
                   class="w-full px-3 py-2 border rounded @error('bearbeiter') border-red-500 @enderror" required>
            @error('bearbeiter')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col sm:flex-row sm:justify-between gap-2">
            <flux:button href="{{ route('projects.index') }}" variant="ghost">
                Abbrechen
            </flux:button>
            <flux:button type="submit" variant="primary">
                Erstellen
            </flux:button>
        </div>
    </form>
</div>
