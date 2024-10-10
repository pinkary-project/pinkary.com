<div @if (auth()->user()?->is($user)) x-data="{
    showSettingsForm: {{ $errors->settings->isEmpty() ? 'false' : 'true' }},
    gradient: '{{ $user->gradient }}',
    link_shape: '{{ $user->link_shape }}',
}" @endif>
    <div class="relative bg-gradient-to-r p-5 text-center dark:text-white text-black">
        <div class="absolute left-0 top-6 flex">
            <x-dropdown-link-profile>
                <x-slot name="trigger">
                    <button
                        x-bind:class="{ 'bg-pink-500 hover:bg-pink-500 text-white hover:text-white': open,
                                        'dark:bg-slate-900 bg-slate-50 dark:hover:bg-slate-800 hover:bg-slate-100 border dark:border-transparent border-slate-200': !open }"
                                    class="mr-2 flex size-10 items-center justify-center rounded-lg dark:text-slate-300 text-slate-600 transition duration-150 ease-in-out "
                    >
                        <x-heroicon-o-share class="size-5" />
                    </button>
                </x-slot>

                <x-slot name="content">
                    <button
                        x-data="shareProfile"
                        x-show="isVisible"
                        x-on:click="share({ url: '{{ route('profile.show', ['username' => $user->username]) }}' })"
                        type="button"
                        class="mr-2 flex size-10 items-center justify-center rounded-lg border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 dark:text-slate-300 text-slate-600 transition duration-150 ease-in-out dark:hover:bg-slate-800 hover:bg-slate-100 dark:hover:text-white hover:text-black"
                    >
                        <x-heroicon-o-link class="size-5" />
                    </button>
                    <button
                        x-data="copyUrl"
                        x-show="isVisible"
                        x-on:click="
                            copyToClipboard(
                                '{{ route('profile.show', ['username' => $user->username]) }}',
                            )
                        "
                        type="button"
                        class="mr-2 flex size-10 items-center justify-center rounded-lg border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 dark:text-slate-300 text-slate-600 transition duration-150 ease-in-out dark:hover:bg-slate-800 hover:bg-slate-100 dark:hover:text-white hover:text-black"
                    >
                        <x-heroicon-o-link class="size-5" />
                    </button>
                    <button
                        x-data="shareProfile"
                        x-on:click="
                            twitter({
                                url: '{{ route('profile.show', ['username' => $user->username]) }}',
                                message: '{{ auth()->user()?->is($user) ? 'Follow me on Pinkary' : "Follow {$user->name} on Pinkary" }}',
                            })
                        "
                        type="button"
                        class="mr-2 flex size-10 items-center justify-center rounded-lg border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 dark:text-slate-300 text-slate-600 transition duration-150 ease-in-out dark:hover:bg-slate-800 hover:bg-slate-100 dark:hover:text-white hover:text-black"
                    >
                        <x-icons.twitter-x class="size-5" />
                    </button>
                </x-slot>
            </x-dropdown-link-profile>
            @if (auth()->user()?->is($user))
                <button
                    class="flex size-10 items-center justify-center rounded-lg border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 dark:text-slate-300 text-slate-600 transition duration-150 ease-in-out dark:hover:bg-slate-800 hover:bg-slate-100 dark:hover:text-white hover:text-black"
                    x-on:click.prevent="$dispatch('open-modal', 'show-qr-code')"
                >
                    <span class="sr-only">See QR Code</span>

                    <x-icons.qr-code class="size-5" />
                </button>
                <x-modal-qr-code />
            @endif
        </div>

        @if (! $user->is(auth()->user()))
            <div class="absolute right-0 top-6 flex">
                @if ($user->followers()->where('follower_id', auth()->id())->exists())
                    <button
                        type="button"
                        wire:click="unfollow({{ $user->id }})"
                        class="flex items-center justify-center rounded-lg border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 px-2 py-1 dark:text-slate-300 text-slate-600 transition duration-150 ease-in-out dark:hover:bg-slate-800 hover:bg-slate-200 dark:hover:text-white hover:text-black"
                    >
                        Following
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="follow({{ $user->id }})"
                        class="flex items-center justify-center rounded-lg border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 px-2 py-1 dark:text-slate-300 text-slate-600 transition duration-150 ease-in-out dark:hover:bg-slate-800 hover:bg-slate-200 dark:hover:text-white hover:text-black"
                    >
                        Follow
                    </button>
                @endif
            </div>
        @endif

        <div class="relative mx-auto h-24 w-24" x-data="{ showAvatar: false }">
            <img
                src="{{ $user->avatar_url }}"
                alt="{{ $user->username }}"
                class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} mx-auto mb-3 size-24 cursor-pointer"
                x-on:click="showAvatar = true"
            />

            <div
                x-show="showAvatar"
                x-cloak
                x-on:click="showAvatar = false"
                class="fixed inset-0 flex items-center justify-center dark:bg-slate-900 bg-slate-50 bg-opacity-75 z-50"
            >
                <img
                    src="{{ $user->avatar_url }}"
                    alt="{{ $user->username }}"
                    class="rounded-lg size-48 md:size-80 sm:size-64"
                />
            </div>


            @if (auth()->user()?->is($user))
                <button
                    class="absolute right-0 top-0 p-0.5 m-0.5 rounded-md border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 dark:text-slate-300 text-slate-500 transition duration-150 ease-in-out dark:hover:bg-slate-800 hover:bg-slate-100 dark:hover:text-white hover:text-black"
                    href="{{ route('profile.edit') }}"
                    wire:navigate
                    title="Upload Avatar"
                >
                    <x-heroicon-o-camera class="size-5" />
                </button>
            @endif
        </div>

        <div class="items center flex items-center justify-center mt-2">
            <h2 class="text-2xl font-bold">{{ $user->name }}</h2>

            @if ($user->is_verified && $user->is_company_verified)
                <x-icons.verified-company
                    :color="$user->right_color"
                    class="ml-1.5 size-6"
                />
            @elseif ($user->is_verified)
                <x-icons.verified
                    :color="$user->right_color"
                    class="ml-1.5 size-6"
                />
            @endif
        </div>

        <a
            class="dark:text-slate-400 text-slate-600"
            href="{{ route('profile.show', ['username' => $user->username]) }}"
            wire:navigate
        >
            <p class="text-sm">{{ '@'.$user->username }}</p>
        </a>

        @if ($user->bio)
            <p class="text-sm">{{ $user->parsed_bio }}</p>
        @elseif (auth()->user()?->is($user))
            <a
                href="{{ route('profile.edit') }}"
                class="text-sm text-slate-500 hover:underline"
                wire:navigate
                >Tell people about yourself</a
            >
        @endif

        <livewire:followers.index :userId="$user->id" />
        <livewire:following.index :userId="$user->id" />

        <div class="mt-2 text-sm">
            <p class="dark:text-slate-400 text-slate-600">
                @if ($user->followers_count > 0)
                    <button x-on:click.prevent="$dispatch('open-modal', 'followers')">
                        <span
                            class="cursor-help"
                            title="{{ Number::format($user->followers_count) }} {{ str('Follower')->plural($user->followers_count) }}"
                        >
                            {{ Number::abbreviate($user->followers_count) }}
                            {{ str('Follower')->plural($user->followers_count) }}
                        </span>
                    </button>

                    <span class="mx-1">•</span>
                @endif

                @if ($user->following_count > 0)
                    <button x-on:click.prevent="$dispatch('open-modal', 'following')">
                        <span
                            class="cursor-help"
                            title="{{ Number::format($user->following_count) }} Following"
                        >
                            {{ Number::abbreviate($user->following_count) }}
                            Following
                        </span>
                    </button>

                    <span class="mx-1">•</span>
                @endif

                @if ($questionsReceivedCount > 0)
                    <span
                        class="cursor-help"
                        title="{{ Number::format($questionsReceivedCount) }} {{ str('Post')->plural($questionsReceivedCount) }}"
                    >
                        {{ Number::abbreviate($questionsReceivedCount) }}
                        {{ str('Post')->plural($questionsReceivedCount) }}
                    </span>

                    <span class="mx-1">•</span>
                @endif

                <span
                    class="cursor-help"
                    title="{{ Number::format($user->views) }} {{ str('View')->plural($user->views) }}"
                >
                    {{ Number::abbreviate($user->views) }} {{ str('View')->plural($user->views) }}
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
                            class="relative h-12 hover:darken-gradient group flex {{ $link->is_visible ? 'bg-gradient-to-r' : 'bg-gray-500' }} overflow-hidden shadow-md"
                            :class="showSettingsForm ? gradient + ' ' + link_shape : '{{ $user->gradient }} {{ $user->link_shape }}'"
                            x-sortable-item="{{ $link->id }}"
                            wire:key="link-{{ $link->id }}"
                            x-data="{ showActions: false }"
                            x-on:click.outside="showActions = false"
                        >
                            <div
                                x-sortable-handle
                                class="absolute left-0 sm:-left-10 top-0 bottom-0 flex w-11 cursor-move items-center justify-center text-slate-50 opacity-50 hover:opacity-100 focus:outline-none group-hover:left-0 transition-all duration-500 z-10"
                            >
                                <x-heroicon-o-bars-3 class="size-6 opacity-100 group-hover:opacity-100 sm:opacity-0" />
                            </div>

                            <div class="flex-grow flex items-center justify-center transition-all duration-500"
                                x-bind:class="{ 'group-hover:-translate-x-full' : showActions }"
                            >
                                <x-links.list-item :$user :$link />
                            </div>

                            <div
                                x-on:click="showActions = !showActions"
                                x-bind:class="{ 'invisible': isDragging }"
                                class="absolute right-0 sm:-right-10 top-0 bottom-0 flex w-11 cursor-pointer items-center justify-center text-slate-50 opacity-50 hover:opacity-100 focus:outline-none transition-all duration-500 z-10 group-hover:right-0"
                            >
                                <x-heroicon-o-chevron-double-left class="size-6 opacity-100 group-hover:opacity-100 sm:opacity-0"
                                    x-bind:class="{ 'rotate-180': showActions }"
                                    x-cloak
                                />
                            </div>

                            <div class="absolute -right-56 top-0 bottom-0 flex items-center justify-center transition-all duration-500 z-5"
                                x-bind:class="{ 'group-hover:inset-0' : showActions }"
                            >
                                <div
                                    class="text-white min-w-fit cursor-help items-center gap-1 text-xs"
                                    title="Clicked {{ Number::format($link->click_count) }} times"
                                    x-bind:class="{ 'invisible': isDragging }"
                                >
                                    {{ Number::abbreviate($link->click_count) }}
                                    {{ str('click')->plural($link->click_count) }}
                                </div>

                                <button
                                    wire:click="setVisibility({{ $link->id }})"
                                    type="button"
                                    class="flex w-10 justify-center text-slate-50 opacity-50 hover:opacity-100 focus:outline-none"
                                >
                                    @if ($link->is_visible)
                                        <x-heroicon-o-eye class="size-5"
                                            x-bind:class="{ 'invisible': isDragging }"
                                        />
                                    @else
                                        <x-heroicon-o-eye-slash class="size-5"
                                            x-bind:class="{ 'invisible': isDragging }"
                                        />
                                    @endif
                                </button>

                                <button
                                    wire:click="$dispatchTo('links.edit', 'link.edit', { link: {{ $link->id }} })"
                                    type="button"
                                    class="flex w-10 justify-center text-slate-50 opacity-50 hover:opacity-100 focus:outline-none"
                                >
                                    <x-heroicon-o-pencil
                                        class="size-5"
                                        x-bind:class="{ 'invisible': isDragging }"
                                    />
                                </button>

                                <form wire:submit="destroy({{ $link->id }})">
                                    <button
                                        onclick="if (!confirm('Are you sure you want to delete this link?')) { return false; }"
                                        type="submit"
                                        class="flex w-10 justify-center text-slate-50 opacity-50 hover:opacity-100 focus:outline-none"
                                    >
                                        <x-heroicon-o-trash
                                            class="size-5 opacity-100 group-hover:opacity-100 sm:opacity-0"
                                            x-bind:class="{ 'invisible': isDragging }"
                                        />
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <x-modal
                    name="link-edit-modal"
                    maxWidth="2xl"
                >
                    <div class="p-10">
                        <livewire:links.edit />
                    </div>
                </x-modal>
            @else
                <div class="space-y-3">
                    @foreach ($links as $link)
                        <div
                            class="{{ $user->link_shape }} {{ $user->gradient }} h-12 hover:darken-gradient flex justify-center bg-gradient-to-r shadow-md"
                            wire:click="click({{ $link->id }})"
                        >
                            <x-links.list-item
                                :$user
                                :$link
                            />
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
            }"
            class="py-4"
        >
            <div>
                <div class="flex gap-2">
                    <button
                        x-on:click="showLinksForm = ! showLinksForm ; showSettingsForm = false"
                        class="hover:darken-gradient flex w-full basis-4/5 items-center justify-center bg-gradient-to-r px-4 py-2 text-sm font-bold text-white transition duration-300 ease-in-out"
                        :class="showSettingsForm ? gradient + ' ' + link_shape : '{{ $user->gradient }} {{ $user->link_shape }}'"
                    >
                        <x-icons.plus class="mr-1.5 size-5" />
                        Add New Link
                    </button>
                    <button
                        x-on:click="showSettingsForm = ! showSettingsForm ; showLinksForm = false"
                        class="hover:darken-gradient flex w-full basis-1/5 items-center justify-center px-4 py-2 font-bold text-white transition duration-300 ease-in-out"
                        :class="showSettingsForm ? 'bg-' + gradient.split(' ')[1].replace('to-', '') + ' ' + link_shape : 'bg-{{ $user->right_color }} {{ $user->link_shape }}'"
                    >
                        <x-heroicon-o-cog-6-tooth class="size-6" />
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
