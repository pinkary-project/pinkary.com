<div
    class="space-y-4"
    @if (auth()->user()?->is($user))
        x-data="{
    showSettingsForm: {{ $errors->settings->isEmpty() ? 'false' : 'true' }},
    gradient: '{{ $user->gradient }}',
    link_shape: '{{ $user->link_shape }}',
}"
    @endif
>
    @if (auth()->user()?->is($user))
        <x-modal-qr-code />
    @endif

    <div class="relative overflow-hidden rounded-md border border-slate-200/70 bg-white/85 p-4 text-center text-slate-950 shadow-xl shadow-slate-900/5 sm:p-5 dark:border-slate-800/30 dark:bg-[#07101f]/95 dark:text-white dark:shadow-black/20">
        <div class="pointer-events-none absolute inset-x-0 top-0 hidden h-32 bg-[radial-gradient(circle_at_top,rgba(244,114,182,0.12),transparent_55%)] dark:block"></div>

        <div class="absolute top-4 left-4 z-10 flex">
            <x-dropdown-link-profile>
                <x-slot name="trigger">
                    <button
                        x-bind:class="{
                            'bg-pink-500 hover:bg-pink-500 text-white hover:text-white': open,
                            'dark:bg-slate-900 bg-white dark:hover:bg-slate-800 hover:bg-slate-100 border dark:border-slate-800 border-slate-200':
                                ! open,
                        }"
                        class="mr-2 flex size-11 items-center justify-center rounded-md text-slate-600 transition duration-150 ease-in-out dark:text-slate-300"
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
                        class="mr-2 flex size-10 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-black dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
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
                        class="mr-2 flex size-10 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-black dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
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
                        class="mr-2 flex size-10 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-black dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                    >
                        <x-icons.twitter-x class="size-5" />
                    </button>
                </x-slot>
            </x-dropdown-link-profile>
            @if (auth()->user()?->is($user))
                <button
                    class="flex size-11 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-black dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                    x-on:click.prevent="$dispatch('open-modal', 'show-qr-code')"
                >
                    <span class="sr-only">See QR Code</span>

                    <x-icons.qr-code class="size-5" />
                </button>
            @endif
        </div>

        @if (! $user->is(auth()->user()))
            <div class="absolute top-4 right-4 z-10 flex">
                @if ($user->followers()->where('follower_id', auth()->id())->exists())
                    <button
                        type="button"
                        wire:click="unfollow({{ $user->id }})"
                        class="flex items-center justify-center rounded-full border border-pink-500 bg-pink-500 px-4 py-2 text-sm font-medium text-white transition duration-150 ease-in-out hover:bg-pink-400"
                    >
                        Following
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="follow({{ $user->id }})"
                        class="flex items-center justify-center rounded-full border border-pink-500 bg-pink-500 px-4 py-2 text-sm font-medium text-white transition duration-150 ease-in-out hover:bg-pink-400"
                    >
                        Follow
                    </button>
                @endif
            </div>
        @endif

        <div class="relative z-10 mx-auto h-20 w-20 sm:h-24 sm:w-24" x-data="{ showAvatar: false }">
            <img
                src="{{ $user->avatar_url }}"
                alt="{{ $user->username }}"
                class="{{ $user->is_company_verified ? 'rounded-3xl' : 'rounded-full' }} mx-auto mb-3 size-20 cursor-pointer border-4 border-white/80 shadow-xl shadow-slate-900/10 dark:border-slate-900/80 dark:shadow-black/30 sm:size-24"
                x-on:click="showAvatar = true"
            />

            <div
                x-show="showAvatar"
                x-cloak
                x-on:click="showAvatar = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-50/75 dark:bg-slate-900/75"
            >
                <img
                    src="{{ $user->avatar_url }}"
                    alt="{{ $user->username }}"
                    class="size-48 rounded-3xl sm:size-64 md:size-80"
                />
            </div>

            @if (auth()->user()?->is($user))
                <button
                    class="absolute top-0 right-0 m-1 rounded-md border border-slate-200 bg-white p-1 text-slate-500 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-black dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                    href="{{ route('profile.edit') }}"
                    wire:navigate
                    title="Upload Avatar"
                >
                    <x-heroicon-o-camera class="size-4" />
                </button>
            @endif
        </div>

        <div class="relative z-10 mt-4 flex items-center justify-center">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl dark:text-white">
                {{ $user->name }}
            </h2>

            @if ($user->is_verified && $user->is_company_verified)
                <x-icons.verified-company :color="$user->right_color" class="ml-1.5 size-6" />
            @elseif ($user->is_verified)
                <x-icons.verified :color="$user->right_color" class="ml-1.5 size-6" />
            @endif
        </div>

        <a
            class="relative z-10 mt-2 inline-flex items-center rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-sm text-slate-600 dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-400"
            href="{{ route('profile.show', ['username' => $user->username]) }}"
            wire:navigate
        >
            <p>{{ '@'.$user->username }}</p>
        </a>

        @if ($user->bio)
            <div class="relative z-10 mx-auto mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                {{ $user->parsed_bio }}
            </div>
        @elseif (auth()->user()?->is($user))
            <a
                href="{{ route('profile.edit') }}"
                class="relative z-10 mt-3 inline-flex text-sm text-slate-500 hover:underline"
                wire:navigate
            >Tell people about yourself</a>
        @endif

        <livewire:followers :userId="$user->id" />
        <livewire:following :userId="$user->id" />

        <div class="relative z-10 mt-4 flex flex-wrap justify-center gap-2 text-sm">
            @if ($user->followers_count > 0)
                <button
                    x-on:click.prevent="$dispatch('open-modal', 'followers')"
                    class="inline-flex items-center rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-slate-600 dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-400"
                >
                    <span
                        class="cursor-help"
                        title="{{ Number::format($user->followers_count) }} {{ str('Follower')->plural($user->followers_count) }}"
                    >
                        {{ Number::abbreviate($user->followers_count) }} {{ str('Follower')->plural($user->followers_count) }}
                    </span>
                </button>
            @endif

            @if ($user->following_count > 0)
                <button
                    x-on:click.prevent="$dispatch('open-modal', 'following')"
                    class="inline-flex items-center rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-slate-600 dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-400"
                >
                    <span class="cursor-help" title="{{ Number::format($user->following_count) }} Following">
                        {{ Number::abbreviate($user->following_count) }} Following
                    </span>
                </button>
            @endif

            @if ($questionsReceivedCount > 0)
                <span
                    class="inline-flex cursor-help items-center rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-slate-600 dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-400"
                    title="{{ Number::format($questionsReceivedCount) }} {{ str('Post')->plural($questionsReceivedCount) }}"
                >
                    {{ Number::abbreviate($questionsReceivedCount) }} {{ str('Post')->plural($questionsReceivedCount) }}
                </span>
            @endif

            <span
                class="inline-flex cursor-help items-center rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-slate-600 dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-400"
                title="{{ Number::format($user->views) }} {{ str('View')->plural($user->views) }}"
            >
                {{ Number::abbreviate($user->views) }} {{ str('View')->plural($user->views) }}
            </span>
        </div>
    </div>
    <div class="border-t border-slate-200/70 pt-4 dark:border-slate-800/70">
        @if ($links->isEmpty())
            @if (auth()->user()?->is($user))
                <div class="rounded-md border border-dashed border-slate-300/80 bg-slate-50/70 p-5 text-center dark:border-slate-700/80 dark:bg-slate-900/50">
                    <p class="text-lg font-medium text-slate-950 dark:text-white">No links yet.</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Add your first link to complete the profile card.
                    </p>
                </div>
            @endif
        @else
            @if (auth()->user()?->is($user))
                <ul
                    x-data="{ isDragging: false }"
                    x-sortable
                    x-on:choose.stop="isDragging = true"
                    x-on:unchoose.stop="isDragging = false"
                    wire:end.stop="storeSort($event.target.sortable.toArray())"
                    class="space-y-2"
                >
                    @foreach ($links as $link)
                        <li
                            class="relative flex h-12 overflow-hidden rounded-[1.75rem] shadow-lg shadow-slate-900/10 hover:darken-gradient group {{ $link->is_visible ? 'bg-linear-to-r' : 'bg-gray-500' }}"
                            :class="showSettingsForm ? gradient + ' ' + link_shape : '{{ $user->gradient }} {{ $user->link_shape }}'"
                            x-sortable-item="{{ $link->id }}"
                            wire:key="link-{{ $link->id }}"
                            x-data="{ showActions: false }"
                            x-on:click.outside="showActions = false"
                        >
                            <div
                                x-sortable-handle
                                class="absolute top-0 bottom-0 left-0 z-10 flex w-11 cursor-move items-center justify-center text-slate-50 opacity-50 transition-all duration-500 group-hover:left-0 hover:opacity-100 focus:outline-none sm:-left-10"
                            >
                                <x-heroicon-o-bars-3 class="size-6 opacity-100 group-hover:opacity-100 sm:opacity-0" />
                            </div>

                            <div
                                class="flex grow items-center justify-center transition-all duration-500"
                                x-bind:class="
                                    showActions ? 'max-sm:-translate-x-full sm:group-hover:-translate-x-full' : ''
                                "
                            >
                                <x-links.list-item :$user :$link />
                            </div>

                            <div
                                x-on:click="showActions = ! showActions"
                                x-bind:class="{ invisible: isDragging }"
                                class="absolute top-0 right-0 bottom-0 z-10 flex w-11 cursor-pointer items-center justify-center text-slate-50 opacity-50 transition-all duration-500 group-hover:right-0 hover:opacity-100 focus:outline-none sm:-right-10"
                            >
                                <x-heroicon-o-chevron-double-left
                                    class="size-6 opacity-100 group-hover:opacity-100 sm:opacity-0"
                                    x-bind:class="{ 'rotate-180': showActions }"
                                    x-cloak
                                />
                            </div>

                            <div
                                class="absolute top-0 -right-56 bottom-0 z-5 flex items-center justify-center transition-all duration-500"
                                x-bind:class="showActions ? 'max-sm:inset-0 sm:group-hover:inset-0' : ''"
                            >
                                <div
                                    class="min-w-fit cursor-help items-center gap-1 text-xs text-white"
                                    title="Clicked {{ Number::format($link->click_count) }} times"
                                    x-bind:class="{ invisible: isDragging }"
                                >
                                    {{ Number::abbreviate($link->click_count) }} {{ str('click')->plural($link->click_count) }}
                                </div>

                                <button
                                    wire:click="setVisibility({{ $link->id }})"
                                    type="button"
                                    class="flex w-10 justify-center text-slate-50 opacity-50 hover:opacity-100 focus:outline-none"
                                >
                                    @if ($link->is_visible)
                                        <x-heroicon-o-eye class="size-5" x-bind:class="{ invisible: isDragging }" />
                                    @else
                                        <x-heroicon-o-eye-slash
                                            class="size-5"
                                            x-bind:class="{ invisible: isDragging }"
                                        />
                                    @endif
                                </button>

                                <button
                                    wire:click="$dispatchTo('links.edit', 'link.edit', { link: {{ $link->id }} })"
                                    type="button"
                                    class="flex w-10 justify-center text-slate-50 opacity-50 hover:opacity-100 focus:outline-none"
                                >
                                    <x-heroicon-o-pencil class="size-5" x-bind:class="{ invisible: isDragging }" />
                                </button>

                                <button
                                    x-on:click="$dispatch('open-modal', 'delete-link'); $dispatch('set-link-id', { id: {{ $link->id }} })"
                                    type="button"
                                    class="flex w-10 justify-center text-slate-50 opacity-50 hover:opacity-100 focus:outline-none"
                                >
                                    <x-heroicon-o-trash
                                        class="size-5 opacity-100 group-hover:opacity-100 sm:opacity-0"
                                        x-bind:class="{ 'invisible': isDragging }"
                                    />
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>

                </ul>

                <x-links.delete-modal />

                <x-modal
                    name="link-edit-modal"
                    maxWidth="2xl"
                >
                    <div class="p-10">
                        <livewire:links.edit />
                    </div>
                </x-modal>
            @else
                <div class="space-y-2">
                    @foreach ($links as $link)
                        <div
                            class="{{ $user->link_shape }} {{ $user->gradient }} h-12 rounded-[1.75rem] hover:darken-gradient flex justify-center bg-linear-to-r shadow-lg shadow-slate-900/10"
                            wire:click="click({{ $link->id }})"
                        >
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
            }"
            class="py-2"
        >
            <div>
                <div class="flex gap-2">
                    <button
                        x-on:click="
                            showLinksForm = ! showLinksForm;
                            showSettingsForm = false;
                        "
                        class="hover:darken-gradient flex w-full basis-4/5 items-center justify-center bg-linear-to-r px-4 py-2 text-sm font-bold text-white transition duration-300 ease-in-out"
                        :class="showSettingsForm ? gradient + ' ' + link_shape : '{{ $user->gradient }} {{ $user->link_shape }}'"
                    >
                        <x-icons.plus class="mr-1.5 size-5" />
                        Add New Link
                    </button>
                    <button
                        x-on:click="
                            showSettingsForm = ! showSettingsForm;
                            showLinksForm = false;
                        "
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
