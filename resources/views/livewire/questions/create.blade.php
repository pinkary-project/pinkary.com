<div
    class="mb-12 pt-4"
    id="questions-create"
    x-data="questionCreate({
        mentionSuggestionsSearch: $wire.entangle('mentionSuggestionsQuery').live,
    })"
>
    <form wire:submit="store">
        <div>
            <div class="relative">
                <x-textarea
                    wire:model="content"
                    placeholder="Ask a question..."
                    maxlength="255"
                    rows="3"
                    x-on:input="handleContentUpdate"
                    x-on:keydown="handleMentionSuggestionsKeyboardInput"
                    x-ref="content"
                    required
                />

                <ul
                    class="absolute inset-x-0 flex flex-col rounded-2xl overflow-y-auto bg-slate-800 shadow-sm border border-slate-700/50 text-sm max-h-64 z-30"
                    x-ref="mentionSuggestionsList"
                    x-show="showMentionSuggestions"
                    x-cloak
                >
                    @forelse ($this->mentionSuggestions() as $i => $user)
                        <li
                            x-data="mentionSuggestionItem({
                                index: @js($i),
                                username: @js($user->username),
                            })"
                            wire:key="mention-suggestion-{{ $user->id }}"
                        >
                            <button
                                type="button"
                                class="w-full"
                                x-on:click="onClick"
                                x-on:mouseover="onMouseover"
                                x-bind:class="{
                                    '!bg-slate-900': highlightedMentionSuggestionIndex === @js($i),
                                }"
                            >
                                <x-user-tile class="!rounded-none" :$user />
                            </button>
                        </li>
                    @empty
                        <li class="flex items-center bg-slate-950 bg-opacity-80 p-4">
                            <p>
                                No users found.
                            </p>
                        </li>
                    @endforelse
                </ul>
            </div>

            <p class="text-right text-xs text-slate-400"><span x-text="$wire.content.length"></span> / 255</p>

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
        </div>
    </form>
</div>
