<div>
    @if ($isOpen)
        <div
            x-data="{ openDeleteModal: true }"
            x-init="() => { $wire.on('comment.deleted', () => openDeleteModal = false) }"
            @close="openDeleteModal = false;"
        >
            <div x-show="openDeleteModal" x-cloak
                class="fixed inset-0 flex items-center justify-center bg-slate-900 bg-opacity-5 bg-clip-padding backdrop-blur-sm backdrop-filter">
                <div x-on:click.outside="openDeleteModal = false" class="w-full max-w-md rounded-lg bg-slate-800 p-8">
                        <div class="text-slate-50 mb-4">
                            <p>Are you sure you want to delete this comment?</p>
                        </div>
                    <div class="flex gap-4 items center">
                        <x-danger-button wire:click="delete">
                            {{ __('Delete') }}
                        </x-danger-button>
                        <x-secondary-button x-on:click="$wire.refresh(); openDeleteModal = false">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
