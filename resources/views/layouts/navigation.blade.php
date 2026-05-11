@php
    $homeRoute = route(auth()->user()?->default_feed->toRouteName() ?? 'home.feed');

    $desktopPanelClasses = 'rounded-3xl border border-white/70 bg-white/80 p-4 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/75 dark:shadow-black/20';
    $desktopItemClasses = 'inline-flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition duration-150 ease-in-out focus:outline-none';
    $desktopIdleClasses = 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white';
    $desktopActiveClasses = 'bg-pink-500 text-white shadow-lg shadow-pink-500/20';
    $mobileItemClasses = 'inline-flex flex-1 items-center justify-center rounded-2xl px-3 py-3 text-sm font-medium transition duration-150 ease-in-out focus:outline-none';
    $mobileIdleClasses = 'text-slate-500 hover:text-slate-950 dark:text-slate-400 dark:hover:text-white';
    $mobileActiveClasses = 'bg-pink-500 text-white';
    $profileIsActive = auth()->check() && request()->routeIs('profile.show') && request()->route('username')?->is(auth()->user());
    $menuLinkClasses = 'flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white';
@endphp

<nav class="w-full">
    <div class="hidden lg:flex lg:h-full lg:flex-col lg:justify-between lg:gap-4">
        <div class="{{ $desktopPanelClasses }}">
            <a
                href="{{ route('home.feed') }}"
                class="inline-flex items-center"
                wire:navigate
            >
                <x-pinkary-logo class="w-32" />
            </a>

            <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-400">
                Ask questions, post updates, and keep your public profile in one polished place.
            </p>

            <div class="mt-6 space-y-1.5">
                @auth
                    <a
                        title="Home"
                        href="{{ $homeRoute }}"
                        class="{{ $desktopItemClasses }} {{ request()->routeIs('home.*') ? $desktopActiveClasses : $desktopIdleClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-home class="h-5 w-5" />
                        <span>Home</span>
                    </a>

                    <a
                        title="Profile"
                        href="{{ route('profile.show', ['username' => auth()->user()->username]) }}"
                        class="{{ $desktopItemClasses }} {{ $profileIsActive ? $desktopActiveClasses : $desktopIdleClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-user class="h-5 w-5" />
                        <span>Profile</span>
                    </a>

                    <a
                        title="Bookmarks"
                        href="{{ route('bookmarks.index') }}"
                        class="{{ $desktopItemClasses }} {{ request()->routeIs('bookmarks.*') ? $desktopActiveClasses : $desktopIdleClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-bookmark class="h-5 w-5" />
                        <span>Bookmarks</span>
                    </a>

                    <a
                        title="Notifications"
                        href="{{ route('notifications.index') }}"
                        class="{{ $desktopItemClasses }} {{ request()->routeIs('notifications.*') ? $desktopActiveClasses : $desktopIdleClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-bell class="h-5 w-5" />
                        <span>Notifications</span>
                        <span class="ml-auto inline-flex min-w-5 justify-center rounded-full bg-slate-950/10 px-1.5 py-0.5 text-xs dark:bg-white/10">
                            <livewire:navigation.notifications-count.show />
                        </span>
                    </a>
                @else
                    <a
                        title="Feed"
                        href="{{ route('home.feed') }}"
                        class="{{ $desktopItemClasses }} {{ request()->routeIs('home.feed') ? $desktopActiveClasses : $desktopIdleClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-home class="h-5 w-5" />
                        <span>Feed</span>
                    </a>

                    <a
                        title="About"
                        href="{{ route('about') }}"
                        class="{{ $desktopItemClasses }} {{ request()->routeIs('about') ? $desktopActiveClasses : $desktopIdleClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-information-circle class="h-5 w-5" />
                        <span>About</span>
                    </a>

                    <a
                        title="Log in"
                        href="{{ route('login') }}"
                        class="{{ $desktopItemClasses }} {{ request()->routeIs('login') ? $desktopActiveClasses : $desktopIdleClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-arrow-right-end-on-rectangle class="h-5 w-5" />
                        <span>Log in</span>
                    </a>

                    <a
                        title="Register"
                        href="{{ route('register') }}"
                        class="{{ $desktopItemClasses }} {{ request()->routeIs('register') ? $desktopActiveClasses : $desktopIdleClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-user-plus class="h-5 w-5" />
                        <span>Register</span>
                    </a>
                @endauth
            </div>
        </div>

        <div class="{{ $desktopPanelClasses }}">
            <div x-data="themeSwitch()">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Appearance</p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Choose your preferred theme.</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-3 py-3 text-slate-500 transition hover:text-slate-950 dark:border-slate-800 dark:text-slate-400 dark:hover:text-white"
                        x-bind:class="theme == 'light' ? 'bg-pink-500 text-white border-pink-500 hover:text-white' : 'bg-white dark:bg-slate-950'"
                        @click="setTheme('light')"
                    >
                        <x-heroicon-o-sun class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-3 py-3 text-slate-500 transition hover:text-slate-950 dark:border-slate-800 dark:text-slate-400 dark:hover:text-white"
                        x-bind:class="theme == 'dark' ? 'bg-pink-500 text-white border-pink-500 hover:text-white' : 'bg-white dark:bg-slate-950'"
                        @click="setTheme('dark')"
                    >
                        <x-heroicon-o-moon class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-3 py-3 text-slate-500 transition hover:text-slate-950 dark:border-slate-800 dark:text-slate-400 dark:hover:text-white"
                        x-bind:class="theme == 'system' ? 'bg-pink-500 text-white border-pink-500 hover:text-white' : 'bg-white dark:bg-slate-950'"
                        @click="setTheme('system')"
                    >
                        <x-heroicon-o-computer-desktop class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <div class="mt-4 space-y-1.5 border-t border-slate-200/70 pt-4 dark:border-slate-800/70">
                <a
                    href="{{ route('about') }}"
                    class="{{ $menuLinkClasses }}"
                    wire:navigate
                >
                    <x-heroicon-o-information-circle class="h-4 w-4" />
                    <span>About</span>
                </a>

                <a
                    href="https://github.com/pinkary-project/pinkary.com"
                    target="_blank"
                    class="{{ $menuLinkClasses }}"
                >
                    <x-heroicon-o-code-bracket class="h-4 w-4" />
                    <span>Source code</span>
                </a>

                @auth
                    <a
                        href="{{ route('profile.edit') }}"
                        class="{{ $menuLinkClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                        <span>Settings</span>
                    </a>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="{{ $menuLinkClasses }} w-full"
                        >
                            <x-heroicon-o-arrow-left-start-on-rectangle class="h-4 w-4" />
                            <span>Log out</span>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-4 z-50 mx-auto flex w-[min(calc(100%-1.5rem),32rem)] items-center gap-2 rounded-[1.75rem] border border-white/70 bg-white/85 p-2 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/85 dark:shadow-black/30 lg:hidden">
        @auth
            <a
                title="Home"
                href="{{ $homeRoute }}"
                class="{{ $mobileItemClasses }} {{ request()->routeIs('home.*') ? $mobileActiveClasses : $mobileIdleClasses }}"
                wire:navigate
            >
                <x-heroicon-o-home class="h-5 w-5" />
            </a>

            <a
                title="Profile"
                href="{{ route('profile.show', ['username' => auth()->user()->username]) }}"
                class="{{ $mobileItemClasses }} {{ $profileIsActive ? $mobileActiveClasses : $mobileIdleClasses }}"
                wire:navigate
            >
                <x-heroicon-o-user class="h-5 w-5" />
            </a>

            <a
                title="Bookmarks"
                href="{{ route('bookmarks.index') }}"
                class="{{ $mobileItemClasses }} {{ request()->routeIs('bookmarks.*') ? $mobileActiveClasses : $mobileIdleClasses }}"
                wire:navigate
            >
                <x-heroicon-o-bookmark class="h-5 w-5" />
            </a>

            <a
                title="Notifications"
                href="{{ route('notifications.index') }}"
                class="{{ $mobileItemClasses }} {{ request()->routeIs('notifications.*') ? $mobileActiveClasses : $mobileIdleClasses }} relative"
                wire:navigate
            >
                <x-heroicon-o-bell class="h-5 w-5" />
                <span class="absolute right-2 top-2 text-[10px] leading-none">
                    <livewire:navigation.notifications-count.show />
                </span>
            </a>
        @else
            <a
                title="Feed"
                href="{{ route('home.feed') }}"
                class="{{ $mobileItemClasses }} {{ request()->routeIs('home.feed') ? $mobileActiveClasses : $mobileIdleClasses }}"
                wire:navigate
            >
                <x-heroicon-o-home class="h-5 w-5" />
            </a>

            <a
                title="Log in"
                href="{{ route('login') }}"
                class="{{ $mobileItemClasses }} {{ request()->routeIs('login') ? $mobileActiveClasses : $mobileIdleClasses }}"
                wire:navigate
            >
                <x-heroicon-o-arrow-right-end-on-rectangle class="h-5 w-5" />
            </a>

            <a
                title="Register"
                href="{{ route('register') }}"
                class="{{ $mobileItemClasses }} {{ request()->routeIs('register') ? $mobileActiveClasses : $mobileIdleClasses }}"
                wire:navigate
            >
                <x-heroicon-o-user-plus class="h-5 w-5" />
            </a>
        @endauth

        <x-dropdown
            align="right"
            width="60"
            :content-classes="'space-y-1 rounded-3xl border border-white/70 bg-white/95 p-2 text-slate-500 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/95 dark:shadow-black/30'"
        >
            <x-slot name="trigger">
                <button
                    title="Menu"
                    class="{{ $mobileItemClasses }} {{ $mobileIdleClasses }} max-w-[3rem]"
                >
                    <x-icons.bars class="size-5" />
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-button x-data="themeSwitch()" class="flex flex-col items-center justify-between rounded-2xl px-3 py-2 dark:hover:bg-transparent hover:bg-transparent">
                    <div class="flex w-full flex-row justify-between gap-2">
                        <div class="flex flex-1 items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 dark:border-slate-800" x-bind:class="theme == 'light' ? 'bg-pink-500 text-white border-pink-500' : 'dark:hover:bg-slate-800/50 hover:bg-slate-100'" @click="setTheme('light')">
                            <x-heroicon-o-sun class="h-4 w-4" />
                        </div>
                        <div class="flex flex-1 items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 dark:border-slate-800" x-bind:class="theme == 'dark' ? 'bg-pink-500 text-white border-pink-500' : 'dark:hover:bg-slate-800/50 hover:bg-slate-100'" @click="setTheme('dark')">
                            <x-heroicon-o-moon class="h-4 w-4" />
                        </div>
                        <div class="flex flex-1 items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 dark:border-slate-800" x-bind:class="theme == 'system' ? 'bg-pink-500 text-white border-pink-500' : 'dark:hover:bg-slate-800/50 hover:bg-slate-100'" @click="setTheme('system')">
                            <x-heroicon-o-computer-desktop class="h-4 w-4" />
                        </div>
                    </div>
                </x-dropdown-button>

                <x-dropdown-link :href="route('about')">
                    {{ __('About') }}
                </x-dropdown-link>

                <x-dropdown-link href="https://github.com/pinkary-project/pinkary.com" target="_blank">
                    {{ __('Source code') }}
                </x-dropdown-link>

                @auth
                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Settings') }}
                    </x-dropdown-link>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        x-data
                    >
                        @csrf

                        <x-dropdown-button onclick="event.preventDefault();this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-button>
                    </form>
                @endauth
            </x-slot>
        </x-dropdown>
    </div>
</nav>
