<div
    class="mb-12 pt-4"
    id="questions-create"
    x-data="questionCreate({
        mentionSuggestionsSearch: $wire.entangle('mentionSuggestionsSearch').live,
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
                    class="absolute inset-x-0 flex flex-col divide-y divide-slate-900 rounded-2xl overflow-y-auto bg-slate-800 shadow-sm border border-slate-700/50 text-sm max-h-64 z-30"
                    x-ref="mentionSuggestionsList"
                    x-show="showMentionSuggestions"
                    x-cloak
                    wire:ignore.self
                >
                    @forelse ($this->mentionSuggestions() as $i => $user)
                        <li wire:key="mention-suggestion-{{ $user->id }}">
                            <button
                                type="button"
                                class="group flex items-center gap-3 border border-slate-900  bg-slate-950 bg-opacity-80 p-4 transition-colors hover:bg-slate-900 w-full"
                                x-data="mentionSuggestionItem({
                                    index: @js($i),
                                    username: @js($user->username),
                                })"
                                x-on:click="onClick"
                                x-on:mouseover="onMouseover"
                                x-bind:class="{
                                    '!bg-slate-900': highlightedMentionSuggestionIndex === @js($i),
                                }"
                            >
                                <figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12 flex-shrink-0 overflow-hidden bg-slate-800 transition-opacity group-hover:opacity-90">
                                    <img
                                        class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12"
                                        src="{{ $user->avatar_url }}"
                                        alt="{{ $user->username }}"
                                    />
                                </figure>
                                <div class="flex flex-col items-start overflow-hidden text-sm">
                                    <div class="flex items-center space-x-2">
                                        <p class="truncate font-medium">
                                            {{ $user->name }}
                                        </p>

                                        @if ($user->is_verified && $user->is_company_verified)
                                            <x-icons.verified-company
                                                :color="$user->right_color"
                                                class="size-4"
                                            />
                                        @elseif ($user->is_verified)
                                            <x-icons.verified
                                                :color="$user->right_color"
                                                class="size-4"
                                            />
                                        @endif
                                    </div>
                                    <p class="truncate text-slate-500 transition-colors group-hover:text-slate-400">
                                        {{ '@'.$user->username }}
                                    </p>
                                </div>
                            </a>
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
