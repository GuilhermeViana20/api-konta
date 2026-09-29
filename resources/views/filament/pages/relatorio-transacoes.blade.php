<x-filament-panels::page>
    <form wire:submit="gerarPdf">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button
                type="submit"
                icon="heroicon-o-arrow-down-tray"
                wire:loading.attr="disabled"
                wire:target="gerarPdf"
            >
                <span wire:loading.remove wire:target="gerarPdf">Gerar PDF</span>
                <span wire:loading wire:target="gerarPdf">Gerando...</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>