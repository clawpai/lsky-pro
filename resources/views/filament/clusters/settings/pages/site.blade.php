<x-filament-panels::page>
    @foreach($this->getForms() as $name => $form)
        <form wire:submit="submit('{{ $name }}')">
            {{ $form }}
        </form>
    @endforeach
</x-filament-panels::page>
