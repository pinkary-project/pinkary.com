<div
    class="mb-12 pt-4"
    id="questions-create"
>
    <form
        wire:submit="store"
        wire:keydown.cmd.enter="store"
        wire:keydown.ctrl.enter="store"
    >
        <div>
            <x-textarea
                wire:model="content"
                placeholder="{{ $this->placeholder }}"
                maxlength="{{ $this->maxContentLength }}"
                rows="3"
                required
            />

            <p class="text-right text-xs text-slate-400"><span x-text="$wire.content.length"></span> / {{ $this->maxContentLength}}</p>

            @error('content')
                <x-input-error
                    :messages="$message"
                    class="my-2"
                />
            @enderror
        </div>
        <div class="mt-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-primary-button
                    class="text-{{ $user->left_color }} border-{{ $user->left_color }}"
                    type="submit"
                >
                    {{ __('Send') }}
                </x-primary-button>
            </div>
            @if (! $this->parentId && ! $this->isSharingUpdate)
                <div class="flex items-center">
                    <x-checkbox
                        wire:model="anonymously"
                        id="anonymously"
                    />

                    <label
                        for="anonymously"
                        class="ml-2 text-slate-400"
                        >Anonymously</label
                    >
                </div>
            @endif
        </div>
    </form>
</div>
