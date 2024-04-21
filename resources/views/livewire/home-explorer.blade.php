<div>
    <x-app-layout>
        <x-slot name="title">{{ $this->sectionTitle }}</x-slot>

        <div class="flex flex-col items-center justify-center">
            <div class="w-full max-w-md overflow-hidden rounded-lg px-2 shadow-md sm:px-0">
                <x-home-menu
                    wire:ignore
                    :current-section="$this->currentSection"
                    :sections="$this->sections"
                />

                @livewire('explorer.'.$this->currentSection)
            </div>
        </div>
    </x-app-layout>
</div>
