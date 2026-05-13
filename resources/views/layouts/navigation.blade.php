@php
    $homeRoute = route(auth()->user()?->default_feed->toRouteName() ?? 'home.feed');

    $desktopItemClasses = 'inline-flex w-full items-center gap-3 rounded-md p-2 text-sm/6 font-semibold transition duration-150 ease-in-out focus:outline-none';
    $desktopIdleClasses = 'text-slate-500 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-[#11192b] dark:hover:text-white';
    $desktopActiveClasses = 'bg-slate-950 text-white dark:bg-[#1a2438]';
    $mobileItemClasses = 'inline-flex flex-1 items-center justify-center rounded-md px-3 py-3 text-sm font-medium transition duration-150 ease-in-out focus:outline-none';
    $mobileIdleClasses = 'text-slate-500 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-[#11192b] dark:hover:text-white';
    $mobileActiveClasses = 'bg-slate-950 text-white dark:bg-[#1a2438]';
    $profileIsActive = auth()->check() && request()->routeIs('profile.show') && request()->route('username')?->is(auth()->user());
    $menuLinkClasses = 'flex items-center gap-2 rounded-md px-4 py-3 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-[#11192b] dark:hover:text-white';
    $menuContentClasses = 'space-y-2 rounded-2xl border border-slate-200/80 bg-white/95 p-2 text-slate-600 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-slate-800/80 dark:bg-[#050d1b]/95 dark:text-slate-300 dark:shadow-black/30';
    $themeSwitchPanelClasses = 'rounded-2xl border border-slate-200/70 bg-slate-50 p-2 dark:border-slate-800/40 dark:bg-[#0b1324]';
    $themeSwitchButtonClasses = 'flex flex-1 items-center justify-center rounded-xl border border-slate-200/70 px-4 py-2 text-slate-500 transition dark:border-slate-800/50 dark:text-slate-300';
@endphp

<nav class="w-full lg:h-screen">
    <div class="hidden border-x border-slate-200/70 bg-white/80 px-6 dark:border-slate-800/30 dark:bg-[#07101f]/80 lg:flex lg:h-screen lg:min-h-screen lg:flex-col lg:justify-between">
        <div class="flex flex-col">
            <a
                href="{{ route('home.feed') }}"
                class="mt-6 inline-flex items-center"
                wire:navigate
            >
                <x-pinkary-logo class="h-12 w-auto" />
            </a>

            <div class="mt-14 space-y-1">
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
                        title="Notifications"
                        href="{{ route('notifications.index') }}"
                        class="{{ $desktopItemClasses }} {{ request()->routeIs('notifications.*') ? $desktopActiveClasses : $desktopIdleClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-bell class="h-5 w-5" />
                        <span>Notifications</span>
                        <livewire:navigation.notifications-count.show class="ml-auto" />
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
                        title="Settings"
                        href="{{ route('profile.edit') }}"
                        class="{{ $desktopItemClasses }} {{ request()->routeIs('profile.edit') ? $desktopActiveClasses : $desktopIdleClasses }}"
                        wire:navigate
                    >
                        <x-heroicon-o-cog-6-tooth class="h-5 w-5" />
                        <span>Settings</span>
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

        @auth
            <div class="border-t border-slate-200/70 py-6 dark:border-slate-800/40">
                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('profile.show', ['username' => auth()->user()->username]) }}"
                        class="flex min-w-0 flex-1 items-center gap-3 rounded-md p-2 transition hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#11192b] dark:hover:text-white"
                        wire:navigate
                    >
                        <img
                            src="{{ auth()->user()->avatar_url }}"
                            alt="{{ auth()->user()->username }}"
                            class="{{ auth()->user()->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0"
                        />

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-950 dark:text-white">
                                {{ auth()->user()->name }}
                            </p>

                            <p class="truncate text-xs text-slate-400">
                                {{ '@'.auth()->user()->username }}
                            </p>
                        </div>
                    </a>

                    <x-dropdown
                        align="right"
                        width="60"
                        dropdown-classes="bottom-full"
                        :content-classes="$menuContentClasses"
                    >
                        <x-slot name="trigger">
                            <button
                                type="button"
                                class="inline-flex size-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-[#11192b] hover:text-white"
                            >
                                <x-heroicon-o-ellipsis-horizontal class="size-5" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div x-data="themeSwitch()" class="{{ $themeSwitchPanelClasses }}">
                                <div class="flex w-full flex-row justify-between gap-2">
                                    <button type="button" class="{{ $themeSwitchButtonClasses }}" x-bind:class="theme == 'light' ? 'border-pink-500 bg-pink-500 text-white' : 'hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#11192b] dark:hover:text-white'" @click="setTheme('light')">
                                        <x-heroicon-o-sun class="h-4 w-4" />
                                    </button>
                                    <button type="button" class="{{ $themeSwitchButtonClasses }}" x-bind:class="theme == 'dark' ? 'border-pink-500 bg-pink-500 text-white' : 'hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#11192b] dark:hover:text-white'" @click="setTheme('dark')">
                                        <x-heroicon-o-moon class="h-4 w-4" />
                                    </button>
                                    <button type="button" class="{{ $themeSwitchButtonClasses }}" x-bind:class="theme == 'system' ? 'border-pink-500 bg-pink-500 text-white' : 'hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#11192b] dark:hover:text-white'" @click="setTheme('system')">
                                        <x-heroicon-o-computer-desktop class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <x-dropdown-link :href="route('about')">
                                {{ __('About') }}
                            </x-dropdown-link>

                            <x-dropdown-link href="https://github.com/pinkary-project/pinkary.com" target="_blank">
                                {{ __('Source code') }}
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
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        @endif
    </div>

    <div class="fixed inset-x-0 bottom-[max(0.75rem,env(safe-area-inset-bottom))] z-[60] mx-auto flex w-[min(calc(100%-1.5rem),32rem)] items-center gap-2 rounded-md border border-slate-200/70 bg-white/95 p-2 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-slate-800/80 dark:bg-[#050d1b]/95 dark:shadow-black/30 lg:hidden">
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
            dropdown-classes="bottom-full"
            :content-classes="$menuContentClasses"
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
                <div x-data="themeSwitch()" class="{{ $themeSwitchPanelClasses }}">
                    <div class="flex w-full flex-row justify-between gap-2">
                        <button type="button" class="{{ $themeSwitchButtonClasses }}" x-bind:class="theme == 'light' ? 'border-pink-500 bg-pink-500 text-white' : 'hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#11192b] dark:hover:text-white'" @click="setTheme('light')">
                            <x-heroicon-o-sun class="h-4 w-4" />
                        </button>
                        <button type="button" class="{{ $themeSwitchButtonClasses }}" x-bind:class="theme == 'dark' ? 'border-pink-500 bg-pink-500 text-white' : 'hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#11192b] dark:hover:text-white'" @click="setTheme('dark')">
                            <x-heroicon-o-moon class="h-4 w-4" />
                        </button>
                        <button type="button" class="{{ $themeSwitchButtonClasses }}" x-bind:class="theme == 'system' ? 'border-pink-500 bg-pink-500 text-white' : 'hover:bg-slate-100 hover:text-slate-950 dark:hover:bg-[#11192b] dark:hover:text-white'" @click="setTheme('system')">
                            <x-heroicon-o-computer-desktop class="h-4 w-4" />
                        </button>
                    </div>
                </div>

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
