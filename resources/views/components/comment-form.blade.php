<form wire:submit="{{ $action }}" {{ $attributes }}>
    <div class="mt-4 flex items-center justify-between">
        <div class="w-full">
            <div class="mb-1">
                <label for="comment" class="sr-only">
                    {{ $action }} comment
                </label>

                <x-textarea
                    id="content"
                    wire:model="content"
                    x-autosize
                    class="max-h-96 h-24 resize-none border-none border-transparent bg-transparent focus:border-transparent text-sm focus:outline-0 focus:ring-0"
                    placeholder="Write your comment..."
                    maxlength="255"
                    rows="3"
                ></x-textarea>

                <div class="flex h-4 items-center justify-between">
                    <div class="flex items-center gap-2">
                        @error('content')
                        <x-input-error :messages="$message" class="ml-4" />
                        @enderror
                    </div>
                    <p class="text-xs text-slate-400">
                        <span x-text="$wire.content.length"></span> / 255
                    </p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between gap-4">
                <div class="ml-4 flex gap-4 items center">
                    @php($user = auth()->user())
                    <x-primary-colorless-button
                        class="text-{{ $user->left_color }} border-{{ $user->left_color }}"
                    >
                        {{ __('Send') }}
                    </x-primary-colorless-button>

                    <x-secondary-button x-on:click="$wire.refresh(); $dispatch('close');">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                </div>
            </div>
        </div>
    </div>
</form>
