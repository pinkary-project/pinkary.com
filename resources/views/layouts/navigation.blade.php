@php
    $navClasses = 'fixed z-50 inset-0 md:inset-auto md:right-0 h-16 flex md:justify-end md:px-4 ';
    $navClasses .= auth()->check() ? ' justify-center' : ' justify-end px-4';
@endphp

<nav>
    <div class="{{ $navClasses }} backdrop-blur-sm md:backdrop-blur-none">
        <div class="flex h-16 justify-between">
            <div
                class="flex items-center space-x-2.5"
                x-data
            >
                @auth
                    <a
                        title="Home"
                        href="{{ route('home.feed') }}"
                        class=""
                        wire:navigate
                    >
                        <button
                            type="button"
                            class="{{ request()->routeIs('home.*') ? 'dark:text-slate-100 text-slate-900' : 'dark:text-slate-500 text-slate-400 dark:hover:text-slate-100 hover:text-slate-900' }} inline-flex items-center rounded-md border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                        >
                            <x-heroicon-o-home class="h-6 w-6"/>
                        </button>
                    </a>

                    <a
                        title="Source code"
                        target="_blank"
                        href="https://github.com/pinkary-project/pinkary.com"
                        class=""
                    >
                        <button
                            type="button"
                            class="dark:text-slate-500 text-slate-400 dark:hover:text-slate-100 hover:text-slate-900 inline-flex items-center rounded-md border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                        >
                            <x-heroicon-o-code-bracket class="h-6 w-6"/>
                        </button>
                    </a>

                    <a
                        title="Profile"
                        href="{{ route('profile.show', ['username' => auth()->user()->username]) }}"
                        class=""
                        wire:navigate
                    >
                        <button
                            type="button"
                            class="{{ request()->fullUrlIs(route('profile.show', ['username' => auth()->user()->username])) ? 'dark:text-slate-100 text-slate-900' : 'dark:text-slate-500 text-slate-400 dark:hover:text-slate-100 hover:text-slate-900' }} inline-flex items-center rounded-md border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                        >
                            <x-heroicon-o-user class="h-6 w-6"/>
                        </button>
                    </a>

                    <a
                        title="Bookmarks"
                        href="{{ route('bookmarks.index') }}"
                        class=""
                        wire:navigate
                    >
                        <button
                            type="button"
                            class="{{ request()->routeIs('bookmarks.*') ? 'dark:text-slate-100 text-slate-900' : 'dark:text-slate-500 text-slate-400 dark:hover:text-slate-100 hover:text-slate-900' }} inline-flex items-center rounded-md border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                        >
                            <x-heroicon-o-bookmark class="h-6 w-6"/>
                        </button>
                    </a>

                    <a
                        title="Notifications"
                        href="{{ route('notifications.index') }}"
                        class=""
                        wire:navigate
                    >
                        <button
                            type="button"
                            class="{{ request()->routeIs('notifications.index') ? 'dark:text-slate-100 text-slate-900' : 'dark:text-slate-500 text-slate-400 dark:hover:text-slate-100 hover:text-slate-900' }} inline-flex items-center rounded-md border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                        >
                            <x-heroicon-o-bell class="h-6 w-6"/>

                            <livewire:navigation.notifications-count.show/>
                        </button>
                    </a>
                @endauth

                <x-dropdown
                    align="right"
                    width="48"
                    :content-classes="'space-y-1 dark:bg-slate-900 bg-slate-100 dark:border-none border border-slate-100 py-1 text-slate-500'"
                >
                    <x-slot name="trigger">
                        <button
                            title="Menu"
                            class="inline-flex items-center rounded-md border dark:border-transparent border-slate-200 dark:bg-slate-900 bg-slate-50 px-3 py-2 text-sm font-medium leading-4 dark:text-slate-500 text-slate-400 transition duration-150 ease-in-out dark:hover:text-slate-100 hover:text-slate-900 focus:outline-none"
                        >
                            <x-icons.bars class="size-6"/>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-button x-data="themeSwitch()" class="flex flex-col items-center justify-between dark:hover:bg-transparent hover:bg-transparent">
                            <div class="flex flex-row justify-between gap-2">
                                <div class="rounded-md px-4 py-2 border dark:border-slate-800 border-slate-200" x-bind:class="theme == 'light' ? 'bg-pink-600 text-slate-50' : 'dark:hover:bg-slate-800/50 hover:bg-slate-200/50'" @click="setTheme('light')">
                                    <x-heroicon-o-sun class="w-4 h-4"/>
                                </div>
                                <div class="rounded-md px-4 py-2 border dark:border-slate-800 border-slate-200" x-bind:class="theme == 'dark' ? 'bg-pink-600 text-slate-50' : 'dark:hover:bg-slate-800/50 hover:bg-slate-200/50'" @click="setTheme('dark')">
                                    <x-heroicon-o-moon class="w-4 h-4"/>
                                </div>
                                <div class="rounded-md px-4 py-2 border dark:border-slate-800 border-slate-200" x-bind:class="theme == 'system' ? 'bg-pink-600 text-slate-50' : 'dark:hover:bg-slate-800/50 hover:bg-slate-200/50'" @click="setTheme('system')">
                                    <x-heroicon-o-computer-desktop class="w-4 h-4"/>
                                </div>
                            </div>
                        </x-dropdown-button>
                        <x-dropdown-link :href="route('about')">
                            {{ __('About') }}
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
                        @else
                            <x-dropdown-link
                                :href="route('home.feed')"
                                :class="request()->routeIs('home.feed') ? 'dark:bg-slate-800 bg-slate-200' : ''"
                            >
                                {{ __('Feed') }}
                            </x-dropdown-link>

                            <x-dropdown-link
                                :href="route('login')"
                                :class="request()->routeIs('login') ? 'dark:bg-slate-800 bg-slate-200' : ''"
                            >
                                {{ __('Log in') }}
                            </x-dropdown-link>

                            <x-dropdown-link
                                :href="route('register')"
                                :class="request()->routeIs('register') ? 'dark:bg-slate-800 bg-slate-200' : ''"
                            >
                                {{ __('Register') }}
                            </x-dropdown-link>
                        @endauth
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
