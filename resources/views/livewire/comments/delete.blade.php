<div>
    <x-comment-modal name="comment.delete.{{ $commentId }}">
        <div class="mb-8 text-slate-50">
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
    </x-comment-modal>
</div>
