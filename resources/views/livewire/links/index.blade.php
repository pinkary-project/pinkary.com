<div>
    <div class="bg-gradient-to-r p-5 text-center text-white">
        <img
            src="{{ $user->avatar ? url($user->avatar) : $user->avatar_url }}"
            alt="{{ $user->username }}"
            class="mx-auto h-24 w-24 rounded-full"
        />

        <div class="items center flex justify-center">
            <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
            @if ($user->is_verified)
                <svg aria-label="Verified" class="text-{{ $user->right_color }} ml-1 mt-2 fill-current saturate-200" height="18" role="img" viewBox="0 0 40 40" width="18"><title>Verified</title><path d="M19.998 3.094 14.638 0l-2.972 5.15H5.432v6.354L0 14.64 3.094 20 0 25.359l5.432 3.137v5.905h5.975L14.638 40l5.36-3.094L25.358 40l3.232-5.6h6.162v-6.01L40 25.359 36.905 20 40 14.641l-5.248-3.03v-6.46h-6.419L25.358 0l-5.36 3.094Zm7.415 11.225 2.254 2.287-11.43 11.5-6.835-6.93 2.244-2.258 4.587 4.581 9.18-9.18Z" fill-rule="evenodd" ></path></svg>
            @endif
        </div>

        <a class="text-gray-400" href="{{ route('profile.show', ['user' => $user->username]) }}" wire:navigate>
            <p class="text-sm">{{ '@'.$user->username }}</p>
        </a>

        @if ($user->bio)
            <p class="text-sm">{{ $user->bio }}</p>
        @elseif (auth()->user()?->is($user))
            <a href="{{ route('profile.edit') }}" class="text-sm text-gray-500 hover:underline" wire:navigate>
                Tell people about yourself
            </a>
        @endif

        <div class="mt-2 text-sm">
            <p class="text-gray-400">
                <span>
                    {{ $questionsReceivedCount }}
                    {{ str('Answer')->plural($questionsReceivedCount) }}
                </span>

                <span class="mx-1">•</span>

                <span>
                    Joined
                    {{ $user->created_at->timezone(auth()->user()?->timezone ?: 'UTC')->format('M Y') }}
                </span>
            </p>
        </div>
    </div>
    <div class="py-5">
        @if ($links->isEmpty())
            @if (auth()->user()?->is($user))
                <p class="mx-2 text-center text-gray-500">No links yet. Add your first link!</p>
            @endif
        @else
            @if (auth()->user()?->is($user))
                <ul
                    x-data="{ isDragging: false }"
                    x-sortable
                    x-on:choose.stop="isDragging = true"
                    x-on:unchoose.stop="isDragging = false"
                    wire:end.stop="storeSort($event.target.sortable.toArray())"
                    class="space-y-3"
                >
                    @foreach ($links as $link)
                        <li
                            class="{{ $user->link_shape }} {{ $user->gradient }} hover:darken-gradient mx-2 flex bg-gradient-to-r"
                            x-sortable-item="{{ $link->id }}"
                            wire:key="link-{{ $link->id }}"
                        >
                            <div
                                x-sortable-handle
                                class="flex w-10 cursor-move items-center justify-center text-gray-300 hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                <x-icons.sortable-handle />
                            </div>

                            <x-links.list-item :$user :$link />

                            <div class="flex items-center justify-center">
                                <form wire:submit="destroy({{ $link->id }})">
                                    <button
                                        type="submit"
                                        class="flex w-10 justify-center text-gray-300 hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    >
                                        <x-icons.trash x-bind:class="{ 'invisible': isDragging }" />
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="space-y-3">
                    {{-- Just listing links --}}
                    @foreach ($links as $link)
                        <div class="{{ $user->link_shape }} {{ $user->gradient }} hover:darken-gradient mx-2 flex bg-gradient-to-r">
                            <x-links.list-item :$user :$link />
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    @if (auth()->user()?->is($user))
        <div
            x-data="{
                showLinksForm: {{ $errors->links->isEmpty() ? 'false' : 'true' }},
                showSettingsForm: {{ $errors->settings->isEmpty() ? 'false' : 'true' }},
            }"
            class="py-4"
        >
            <div>
                <div class="mx-2 flex gap-2">
                    <button
                        @click="showLinksForm = ! showLinksForm ; showSettingsForm = false"
                        class="bg-{{ $user->left_color }} {{ $user->link_shape }} hover:darken-gradient flex w-full basis-4/5 items-center justify-center px-4 py-2 font-bold text-white transition duration-300 ease-in-out"
                    >
                        <svg class="mr-2 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" ></path></svg>
                        Add New Link
                    </button>
                    <button
                        @click="showSettingsForm = ! showSettingsForm ; showLinksForm = false"
                        class="{{ $user->gradient }} hover:darken-gradient {{ $user->link_shape }} flex w-full basis-1/5 items-center justify-center bg-gradient-to-r px-4 py-2 font-bold text-white transition duration-300 ease-in-out"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    </button>
                </div>

                <div
                    x-show="showLinksForm"
                    x-transition:enter="transition duration-300 ease-out"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition duration-300 ease-in"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mx-2 mt-4"
                    x-cloak
                >
                    <livewire:links.create :userId="$user->id" />
                </div>

                <div
                    x-show="showSettingsForm"
                    x-transition:enter="transition duration-300 ease-out"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition duration-300 ease-in"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mx-2 mt-4"
                    x-cloak
                >
                    <livewire:link-settings.edit />
                </div>
            </div>
        </div>
    @endif
</div>
