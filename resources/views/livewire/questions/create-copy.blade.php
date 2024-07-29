<div
    class="mb-12 pt-4"
    id="questions-create"
>
    <form wire:submit="store">
        <div>
            <x-textarea
                wire:model="content"
                placeholder="{{ $this->isSharingUpdate ? 'Share an update...' : 'Ask a question...' }}"
                maxlength="{{ $this->maxContentLength }}"
                rows="3"
                required
                x-data="{ focusTextarea() { this.$refs.textarea.focus(); }}"
                x-ref="textarea"
                x-on:tag-selected.window="focusTextarea"
                x-on:input.debounce="$wire.handleInputChange($event.target.value)"
                x-on:keydown.enter="console.log('enter')"
            />

            <p class="text-right text-xs text-slate-400"><span x-text="$wire.content.length"></span> / {{ $this->maxContentLength}}</p>

            @error('content')
                <x-input-error
                    :messages="$message"
                    class="my-2"
                />
            @enderror

            <div class="relative h-auto">
                @if ($showTagsDropdown)
                    <ul class="absolute mt-2 w-full h-52 overflow-y-auto p-3 z-20 text-white caret-white focus:border-pink-500 border-slate-700 bg-slate-800 backdrop-blur-sm focus:ring-slate-900 rounded-lg shadow-sm sm:text-sm">
                        @forelse ($tags as $tag)
                                <li class="p-2 pb-4 rounded-md hover:bg-slate-900 cursor-pointer" wire:click="selectTag('{{ $tag["name"] }}')">
                                    <span class="text-md block font-semibold">#{{ $tag['name'] }}</span>
                                    @if ($tag['is_trending'])
                                        <span class="text-sm font-medium mt-2">Trending</span>
                                    @endif
                                </li>
                        @empty
                            <li class="p-2 pb-4 rounded-md hover:bg-slate-900 cursor-pointer" wire:click="selectTag('{{ $customTag }}')">
                                <span class="text-md block font-semibold">#{{ $customTag }}</span>
                            </li>
                        @endforelse
                    </ul>
                @endif
            </div>
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
            @if (! $this->isSharingUpdate)
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
