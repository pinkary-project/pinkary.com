<div class="border-l border-slate-900">
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
                        class="h-24 max-h-96 resize-none border-none border-transparent bg-transparent text-sm focus:border-transparent focus:outline-0 focus:ring-0"
                        placeholder="Write your comment..."
                        maxlength="255"
                        rows="3"
                    ></x-textarea>

                    <div class="flex h-4 items-center justify-between">
                        <div class="flex items-center gap-2">
                            @error('content')
                            <x-input-error :messages="$message" class="ml-4"/>
                            @enderror
                        </div>
                        <p class="text-xs text-slate-400">
                            <span x-text="$wire.content.length"></span> / 255
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between gap-4">
                    <div class="ml-4 flex gap-4 items center">
                        <x-primary-button>
                            {{ __('Send') }}
                        </x-primary-button>

                        <x-secondary-button x-on:click="$wire.refresh(); show = false;">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
