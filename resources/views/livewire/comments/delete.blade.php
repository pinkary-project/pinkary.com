<div>
    <x-modal
        max-width="md"
        show-close-button="false"
        name="comment.delete.{{ $commentId }}">
        <div class="p-8">
            <h2 class="font-medium text-slate-50 text-md">Delete Comment</h2>
            <div class="my-4 text-slate-50">
                <p>Are you sure you want to delete this comment?</p>
            </div>
            <div class="flex items-center justify-end gap-4">
                <x-danger-button wire:click="delete">
                    {{ __('Delete') }}
                </x-danger-button>
                <x-secondary-button
                    x-on:click="show = false;"
                >
                    {{ __('Cancel') }}
                </x-secondary-button>
            </div>
        </div>
    </x-modal>
</div>
