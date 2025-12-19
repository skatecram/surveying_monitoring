<flux:container class="max-w-2xl mx-auto">
    <flux:heading size="xl" class="mb-6">Neues Projekt erstellen</flux:heading>

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>Auftragsnummer:</flux:label>
            <flux:input wire:model="number" required />
            <flux:error name="number" />
        </flux:field>

        <flux:field>
            <flux:label>Auftrag (Projektname):</flux:label>
            <flux:input wire:model="name" required />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Bearbeiter:</flux:label>
            <flux:input wire:model="bearbeiter" required />
            <flux:error name="bearbeiter" />
        </flux:field>

        <div class="flex flex-col sm:flex-row sm:justify-between gap-2">
            <flux:button href="{{ route('projects.index') }}" variant="ghost">
                Abbrechen
            </flux:button>
            <flux:button type="submit" variant="primary">
                Erstellen
            </flux:button>
        </div>
    </form>
</flux:container>
