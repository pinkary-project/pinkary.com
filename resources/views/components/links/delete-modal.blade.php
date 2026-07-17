<x-modal name="delete-link" max-width="md">
    <div class="p-8" x-data="{ linkId: null }" @set-link-id.window="linkId = $event.detail.id">
        <h2 class="text-lg font-medium text-slate-950 dark:text-slate-50">Delete Link</h2>
        <div class="mt-4 text-slate-500 dark:text-slate-400">
            <p>Are you sure you want to delete this link?</p>
        </div>
        <div class="mt-4 flex items-center justify-between">
            <x-secondary-button x-on:click="$dispatch('close-modal', 'delete-link')"> Cancel </x-secondary-button>
            <x-primary-button wire:click="destroy(linkId)"> Delete </x-primary-button>
        </div>
    </div>
</x-modal>
