<div>
    <div class="relative bg-gradient-to-r p-5 text-center text-white">
        <div class="absolute right-4 sm:right-0 top-0 flex space-x-1.5">
            <button
                x-data="shareProfile"
                x-show="isVisible"
                @click="
                                    share({
                                        url: '{{ route('profile.show', ['user' => request()->route('user')->username]) }}'
                                    })
                                "
                type="button"
                class="duration-150 flex rounded-lg bg-gray-900 p-1 text-gray-300 transition ease-in-out hover:bg-gray-800 hover:text-white"
            >
                <x-icons.share class="h-6 w-6"/>
            </button>
            <button
                x-data="copyUrl"
                x-show="isVisible"
                @click="copyToClipboard('{{ route('profile.show', ['user' => request()->route('user')->username]) }}')"
                type="button"
                class="duration-150 flex rounded-lg bg-gray-900 p-1 text-gray-300 transition ease-in-out hover:bg-gray-800 hover:text-white"
            >
                <x-icons.link class="h-6 w-6"/>
            </button>
            @if (auth()->user()?->is($user))
                <a
                    href="{{ route('qr-code.download') }}"
                    class="duration-150 flex rounded-lg bg-gray-900 p-1 text-gray-300 transition ease-in-out hover:bg-gray-800 hover:text-white"
                    download
                >
                    <span class="sr-only">Download QR Code</span>

                    <x-icons.qr-code class="size-6 shrink-0"/>
                </a>
            @endif
        </div>

        <img
            src="{{ $user->avatar ? url($user->avatar) : $user->avatar_url }}"
            alt="{{ $user->username }}"
            class="mx-auto h-24 w-24 rounded-full"
        />

        <div class="items center flex justify-center items-center">
            <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
            @if ($user->is_verified)
                <x-icons.verified :color="$user->right_color" class="ml-1.5 h-6 w-6"/>
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
                            class="{{ $user->link_shape }} {{ $user->gradient }} hover:darken-gradient mx-2 flex bg-gradient-to-r group"
                            x-sortable-item="{{ $link->id }}"
                            wire:key="link-{{ $link->id }}"
                        >
                            <div
                                x-sortable-handle
                                class="flex w-10 cursor-move items-center justify-center text-gray-300 hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                <x-icons.sortable-handle class="size-3 opacity-0 group-hover:opacity-100"/>
                            </div>

                            <x-links.list-item :$user :$link/>

                            <div class="flex items-center justify-center">
                                <form wire:submit="destroy({{ $link->id }})">
                                    <button
                                        type="submit"
                                        class="flex w-10 justify-center text-gray-300 hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    >
                                        <x-icons.trash class="size-3 group-hover:opacity-100 opacity-0"
                                                       x-bind:class="{ 'invisible': isDragging }"/>
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
                        <div
                            class="{{ $user->link_shape }} {{ $user->gradient }} hover:darken-gradient mx-2 flex bg-gradient-to-r">
                            <x-links.list-item :$user :$link/>
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
                        <x-icons.plus class="mr-2 h-6 w-6"/>
                        Add New Link
                    </button>
                    <button
                        @click="showSettingsForm = ! showSettingsForm ; showLinksForm = false"
                        class="{{ $user->gradient }} hover:darken-gradient {{ $user->link_shape }} flex w-full basis-1/5 items-center justify-center bg-gradient-to-r px-4 py-2 font-bold text-white transition duration-300 ease-in-out"
                    >
                        <x-icons.cog class="h-6 w-6"/>
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
                    <livewire:links.create :userId="$user->id"/>
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
                    <livewire:link-settings.edit/>
                </div>
            </div>
        </div>
    @endif
</div>
