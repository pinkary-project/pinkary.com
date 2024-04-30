<div @if (auth()->user()?->is($user)) x-data="{
    showSettingsForm: {{ $errors->settings->isEmpty() ? 'false' : 'true' }},
    gradient: '{{ $user->gradient }}',
    link_shape: '{{ $user->link_shape }}',
}" @endif>
    <div class="relative bg-gradient-to-r p-5 text-center text-white">
        <div class="absolute left-0 top-6 flex">
            <button
                x-cloak
                x-data="shareProfile"
                x-show="isVisible"
                x-on:click="share({ url: '{{ route('profile.show', ['username' => $user->username]) }}' })"
                type="button"
                class="mr-2 flex size-10 items-center justify-center rounded-lg bg-slate-900 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white"
            >
                <x-icons.share class="size-5" />
            </button>
            <button
                x-cloak
                x-data="copyUrl"
                x-show="isVisible"
                x-on:click="
                    copyToClipboard(
                        '{{ route('profile.show', ['username' => $user->username]) }}',
                    )
                "
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

        @if (! $user->is(auth()->user()))
            <div class="absolute right-0 top-6 flex">
                @if ($user->followers()->where('follower_id', auth()->id())->exists())
                    <button
                        type="button"
                        wire:click="unfollow({{ $user->id }})"
                        class="flex items-center justify-center rounded-lg bg-slate-900 px-2 py-1 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white"
                    >
                        Following
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="follow({{ $user->id }})"
                        class="flex items-center justify-center rounded-lg bg-slate-900 px-2 py-1 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white"
                    >
                        Follow
                    </button>
                @endif
            </div>
        @endif

        <div class="relative mx-auto h-24 w-24">
            <img
                src="{{ $user->avatar_url }}"
                alt="{{ $user->username }}"
                class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} mx-auto mb-3 size-24"
            />
            @if (auth()->user()?->is($user))
                <button
                    class="absolute right-0 top-0 rounded bg-slate-900 text-slate-300 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white"
                    href="{{ route('profile.edit') }}"
                    wire:navigate
                    title="Upload Avatar"
                >
                    <x-icons.camera class="size-5" />
                </button>
            @endif
        </div>

        <div class="items center flex items-center justify-center">
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
            class="text-slate-400"
            href="{{ route('profile.show', ['username' => $user->username]) }}"
            wire:navigate
        >
            <p class="text-sm">{{ '@'.$user->username }}</p>
        </a>

        @if ($user->bio)
            <p class="text-sm">{{ $user->bio }}</p>
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
            <p class="text-slate-400">
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
                        title="{{ Number::format($questionsReceivedCount) }} {{ str('Answer')->plural($questionsReceivedCount) }}"
                    >
                        {{ Number::abbreviate($questionsReceivedCount) }}
                        {{ str('Answer')->plural($questionsReceivedCount) }}
                    </span>

                    <span class="mx-1">•</span>
                @endif

                <span
                    class="cursor-help"
                    title="{{ Number::format($user->views) }} {{ str('view')->plural($user->views) }}"
                >
                    {{ Number::abbreviate($user->views) }} {{ str('view')->plural($user->views) }}
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
                            :class="showSettingsForm && gradient + ' ' + link_shape"
                            x-sortable-item="{{ $link->id }}"
                            wire:key="link-{{ $link->id }}"
                        >
                            <div
                                x-sortable-handle
                                class="flex w-11 cursor-move items-center justify-center text-slate-300 opacity-50 hover:opacity-100 focus:outline-none"
                            >
                                <x-icons.sortable-handle class="size-6 opacity-100 group-hover:opacity-100 sm:opacity-0" />
                            </div>

                            <x-links.list-item
                                :$user
                                :$link
                            />

                            <div class="flex items-center justify-center">
                                <div
                                    class="hidden min-w-fit cursor-help items-center gap-1 text-xs group-hover:flex"
                                    title="Clicked {{ Number::format($link->click_count) }} times"
                                >
                                    {{ Number::abbreviate($link->click_count) }}
                                    {{ str('click')->plural($link->click_count) }}
                                </div>
                                <form wire:submit="destroy({{ $link->id }})">
                                    <button
                                        onclick="if (!confirm('Are you sure you want to delete this link?')) { return false; }"
                                        type="submit"
                                        class="flex w-10 justify-center text-slate-300 opacity-50 hover:opacity-100 focus:outline-none"
                                    >
                                        <x-icons.trash
                                            class="size-5 opacity-100 group-hover:opacity-100 sm:opacity-0"
                                            x-bind:class="{ 'invisible': isDragging }"
                                        />
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="space-y-3">
                    @foreach ($links as $link)
                        <div
                            class="{{ $user->link_shape }} {{ $user->gradient }} hover:darken-gradient flex bg-gradient-to-r"
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
                        class="{{ $user->gradient }} {{ $user->link_shape }} hover:darken-gradient flex w-full basis-4/5 items-center justify-center bg-gradient-to-r px-4 py-2 text-sm font-bold text-white transition duration-300 ease-in-out"
                        :class="showSettingsForm && gradient + ' ' + link_shape"
                    >
                        <x-icons.plus class="mr-1.5 size-5" />
                        Add New Link
                    </button>
                    <button
                        x-on:click="showSettingsForm = ! showSettingsForm ; showLinksForm = false"
                        class="bg-{{ $user->right_color }} hover:darken-gradient {{ $user->link_shape }} flex w-full basis-1/5 items-center justify-center px-4 py-2 font-bold text-white transition duration-300 ease-in-out"
                        :class="showSettingsForm && 'bg-' + gradient.split(' ')[1].replace('to-', '') + ' ' + link_shape"
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
