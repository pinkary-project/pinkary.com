<div class="p-4 sm:p-6">
    <form wire:submit="store" class="space-y-6">
        <div>
            <x-input-label for="name" :value="__('Feed Name')" />
            <x-text-input
                id="name"
                wire:model="name"
                type="text"
                class="mt-1 block w-full"
                placeholder="e.g. Laravel Builders, AI & Open Source"
                required
                autofocus
                maxlength="60"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" :value="__('Description (optional)')" />
            <x-textarea
                id="description"
                wire:model="description"
                class="mt-1 block w-full"
                placeholder="What is this feed about?"
                maxlength="255"
                rows="2"
            />
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div>
            <x-input-label :value="__('Included Topics')" />
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($chosenTopics as $topic)
                    <span class="inline-flex items-center gap-1 rounded-full bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-700 dark:bg-pink-950/40 dark:text-pink-300">
                        #{{ $topic->name }}
                        <button
                            type="button"
                            wire:click="removeTopic({{ $topic->id }})"
                            class="text-pink-500 hover:text-pink-700 dark:hover:text-pink-200"
                        >
                            <x-heroicon-m-x-mark class="size-3.5" />
                        </button>
                    </span>
                @endforeach
            </div>

            <div class="relative mt-2">
                <input
                    type="text"
                    wire:model.live.debounce.200ms="topicSearch"
                    placeholder="Search topics to add..."
                    class="w-full rounded-md border border-slate-200/70 bg-white px-3 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:border-pink-500 focus:ring-0 focus:outline-none dark:border-slate-800/40 dark:bg-[#10182b] dark:text-white dark:placeholder:text-slate-500"
                />

                @if (! empty($searchedTopics) || (mb_trim($topicSearch) !== '' && ! collect($searchedTopics)->contains('name', mb_trim($topicSearch))))
                    <div class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-md border border-slate-200 bg-white p-1 shadow-lg dark:border-slate-800 dark:bg-[#0b1324]">
                        @if (mb_trim($topicSearch) !== '' && ! collect($searchedTopics)->contains('name', mb_trim($topicSearch)))
                            <button
                                type="button"
                                wire:click="addOrCreateTopic('{{ addslashes(mb_trim($topicSearch)) }}')"
                                class="flex w-full items-center gap-1.5 rounded px-3 py-1.5 text-left text-xs font-semibold text-pink-600 hover:bg-pink-50 dark:text-pink-400 dark:hover:bg-[#16203a]"
                            >
                                <x-heroicon-m-plus class="size-3.5" />
                                <span>Create "#{{ mb_trim($topicSearch) }}"</span>
                            </button>
                        @endif

                        @foreach ($searchedTopics as $topic)
                            <button
                                type="button"
                                wire:click="addTopic({{ $topic->id }})"
                                class="flex w-full items-center justify-between rounded px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#16203a]"
                            >
                                <span class="font-medium">#{{ $topic->name }}</span>
                                <x-heroicon-m-plus class="size-3.5 text-slate-400" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div>
            <x-input-label :value="__('Included People')" />
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($chosenPeople as $person)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-[#11192b] dark:text-slate-300">
                        <img
                            src="{{ $person->avatar_url }}"
                            alt="{{ $person->username }}"
                            class="size-4 rounded-full"
                        />
                        <span>{{ '@'.$person->username }}</span>
                        <button
                            type="button"
                            wire:click="removePerson({{ $person->id }})"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <x-heroicon-m-x-mark class="size-3.5" />
                        </button>
                    </span>
                @endforeach
            </div>

            <div class="relative mt-2">
                <input
                    type="text"
                    wire:model.live.debounce.200ms="peopleSearch"
                    placeholder="Search people to add..."
                    class="w-full rounded-md border border-slate-200/70 bg-white px-3 py-2 text-xs text-slate-900 placeholder:text-slate-400 focus:border-pink-500 focus:ring-0 focus:outline-none dark:border-slate-800/40 dark:bg-[#10182b] dark:text-white dark:placeholder:text-slate-500"
                />

                @if (! empty($searchedPeople))
                    <div class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-md border border-slate-200 bg-white p-1 shadow-lg dark:border-slate-800 dark:bg-[#0b1324]">
                        @foreach ($searchedPeople as $person)
                            <button
                                type="button"
                                wire:click="addPerson({{ $person->id }})"
                                class="flex w-full items-center gap-2 rounded px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-[#16203a]"
                            >
                                <img
                                    src="{{ $person->avatar_url }}"
                                    alt="{{ $person->username }}"
                                    class="size-5 rounded-full"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium">{{ $person->name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ '@'.$person->username }}</p>
                                </div>
                                <x-heroicon-m-plus class="size-3.5 text-slate-400" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @error('membership')
            <p class="text-xs text-red-500">{{ $message }}</p>
        @enderror

        <div>
            <x-input-label :value="__('Visibility')" />
            <div class="mt-2 space-y-2">
                <label class="flex items-start gap-3 rounded-lg border border-slate-200/70 p-3 transition hover:bg-slate-50 dark:border-slate-800/40 dark:hover:bg-[#0b1324]">
                    <input
                        type="radio"
                        wire:model="visibility"
                        value="public"
                        class="mt-0.5 text-pink-600 focus:ring-pink-500"
                    />
                    <div>
                        <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Public') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Anyone can view and follow this feed.') }}
                        </p>
                    </div>
                </label>

                <label class="flex items-start gap-3 rounded-lg border border-slate-200/70 p-3 transition hover:bg-slate-50 dark:border-slate-800/40 dark:hover:bg-[#0b1324]">
                    <input
                        type="radio"
                        wire:model="visibility"
                        value="private"
                        class="mt-0.5 text-pink-600 focus:ring-pink-500"
                    />
                    <div>
                        <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ __('Private') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Only you can view and use this feed.') }}
                        </p>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-200/70 pt-4 dark:border-slate-800/30">
            <a
                href="{{ route('feeds.index') }}"
                class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-[#162038]"
                wire:navigate
            >
                {{ __('Cancel') }}
            </a>

            <x-primary-button type="submit"> {{ __('Create Feed') }} </x-primary-button>
        </div>
    </form>
</div>
