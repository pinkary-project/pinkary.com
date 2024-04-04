<div>
    <div class="relative bg-gradient-to-r p-5 text-center text-white">
        <div class="absolute left-0 top-6 flex">
            <button
                x-cloak
                x-data="shareProfile"
                x-show="isVisible"
                @click="share({ url: '{{ route('profile.show', ['username' => $user->username]) }}' })"
                type="button"
                class="mr-2 flex size-10 items-center justify-center rounded-lg bg-slate-900 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white"
            >
                <x-icons.share class="size-5" />
            </button>
            <button
                x-cloak
                x-data="copyUrl"
                x-show="isVisible"
                @click="copyToClipboard('{{ route('profile.show', ['username' => $user->username]) }}')"
                type="button"
                class="mr-2 flex size-10 items-center justify-center rounded-lg bg-slate-900 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white"
            >
                <x-icons.link class="size-5" />
            </button>
            @if (auth()->user()?->is($user))
                <button
                    class="flex size-10 items-center justify-center rounded-lg bg-slate-900 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white"
                    x-on:click.prevent="$dispatch('open-modal', 'show-qr-code')"
                >
                    <span class="sr-only">See QR Code</span>

                    <x-icons.qr-code class="size-5" />
                </button>
                <x-modal-qr-code />
            @endif
        </div>

        @if(! $user->is(auth()->user()))
            <div class="absolute right-0 top-6 flex">
                @if(auth()->user()->follows($user))
                    <button type="button"
                            wire:click="unfollow({{ $user->id }})"
                            class="px-2 py-1 flex items-center justify-center rounded-lg bg-slate-900 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white">
                            Following
                    </button>
                @else
                    <button type="button"
                            wire:click="follow({{ $user->id }})"
                            class="px-2 py-1 flex items-center justify-center rounded-lg bg-slate-900 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white">
                        Follow
                    </button>
                @endif
            </div>
        @endif

        <img
            src="{{ $user->avatar ? url($user->avatar) : $user->avatar_url }}"
            alt="{{ $user->username }}"
            class="mx-auto mb-3 size-24 rounded-full"
        />

        <div class="items center flex items-center justify-center">
            <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
            @if ($user->is_verified)
                <x-icons.verified :color="$user->right_color" class="ml-1.5 size-6" />
            @endif
        </div>

        <a class="text-slate-400" href="{{ route('profile.show', ['username' => $user->username]) }}" wire:navigate>
            <p class="text-sm">{{ '@'.$user->username }}</p>
        </a>

        @if ($user->bio)
            <p class="text-sm">{{ $user->bio }}</p>
        @elseif (auth()->user()?->is($user))
            <a href="{{ route('profile.edit') }}" class="text-sm text-slate-500 hover:underline" wire:navigate>Tell people about yourself</a>
        @endif

        <div class="mt-2 text-sm">
            <p class="text-slate-400">
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
                <p class="mx-2 text-center text-slate-500">No links yet. Add your first link!</p>
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
                            class="{{ $user->link_shape }} {{ $user->gradient }} hover:darken-gradient group flex bg-gradient-to-r"
                            x-sortable-item="{{ $link->id }}"
                            wire:key="link-{{ $link->id }}"
                        >
                            <div
                                x-sortable-handle
                                class="flex w-11 cursor-move items-center justify-center text-slate-300 opacity-50 hover:opacity-100 focus:outline-none"
                            >
                                <x-icons.sortable-handle class="size-6 opacity-0 group-hover:opacity-100" />
                            </div>

                            <x-links.list-item :$user :$link />

                            <div class="flex items-center justify-center">
                                <form wire:submit="destroy({{ $link->id }})">
                                    <button
                                        onclick="if (!confirm('Are you sure you want to delete this link?')) { return false; }"
                                        type="submit"
                                        class="flex w-10 justify-center text-slate-300 opacity-50 hover:opacity-100 focus:outline-none"
                                    >
                                        <x-icons.trash class="size-5 opacity-0 group-hover:opacity-100" x-bind:class="{ 'invisible': isDragging }" />
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
                        <div class="{{ $user->link_shape }} {{ $user->gradient }} hover:darken-gradient flex bg-gradient-to-r">
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
                <div class="flex gap-2">
                    <button
                        @click="showLinksForm = ! showLinksForm ; showSettingsForm = false"
                        class="bg-{{ $user->left_color }} {{ $user->link_shape }} hover:darken-gradient flex w-full basis-4/5 items-center justify-center px-4 py-2 text-sm font-bold text-white transition duration-300 ease-in-out"
                    >
                        <x-icons.plus class="mr-1.5 size-5" />
                        Add New Link
                    </button>
                    <button
                        @click="showSettingsForm = ! showSettingsForm ; showLinksForm = false"
                        class="{{ $user->gradient }} hover:darken-gradient {{ $user->link_shape }} flex w-full basis-1/5 items-center justify-center bg-gradient-to-r px-4 py-2 font-bold text-white transition duration-300 ease-in-out"
                    >
                        <x-icons.cog class="size-6" />
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
                    class="mt-4"
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
