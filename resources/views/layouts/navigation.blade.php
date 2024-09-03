@php
    $navClasses = 'absolute sm:fixed z-50 inset-0 h-0 flex md:justify-end md:px-4 ';
    $navClasses .= auth()->check() ? ' justify-center' : ' justify-end px-4';
@endphp

<nav class="fixed bottom-0 z-50 w-full sm:relative">
    <div class="{{ $navClasses }}">
        <div class="flex items-end justify-between w-full h-16 px-0 -translate-y-full sm:items-start sm:translate-y-0 sm:items-center sm:w-auto">
            <div
                class="flex items-center sm:divide-x-0 border-t sm:border-t-0 border-slate-800 divide-x divide-slate-800 sm:space-x-2.5 sm:w-auto w-full"
                x-data
            >
                @auth
                    <a
                        title="Home"
                        href="{{ route('home.feed') }}"
                        class="flex-1"
                        wire:navigate
                    >
                        <button
                            type="button"
                            class="{{ request()->routeIs('home.*') ? 'text-slate-100' : 'text-slate-500 hover:text-slate-100' }} inline-flex items-center justify-center sm:rounded-md border border-transparent bg-slate-900 px-3 py-3.5 sm:py-2 text-sm font-medium leading-4 transition sm:w-auto w-full duration-150 ease-in-out focus:outline-none"
                        >
                            <x-heroicon-o-home class="w-6 h-6"/>
                        </button>
                    </a>

                    <a
                        title="Source code"
                        target="_blank"
                        href="https://github.com/pinkary-project/pinkary.com"
                        class="flex-1"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center justify-center w-full px-3 py-3.5 text-sm font-medium leading-4 transition duration-150 ease-in-out border border-transparent sm:py-2 sm:rounded-md text-slate-500 hover:text-slate-100 bg-slate-900 sm:w-auto focus:outline-none"
                        >
                            <x-heroicon-o-code-bracket class="w-6 h-6"/>
                        </button>
                    </a>

                    <a
                        title="Profile"
                        href="{{ route('profile.show', ['username' => auth()->user()->username]) }}"
                        class="flex-1"
                        wire:navigate
                    >
                        <button
                            type="button"
                            class="{{ request()->fullUrlIs(route('profile.show', ['username' => auth()->user()->username])) ? 'text-slate-100' : 'text-slate-500 hover:text-slate-100' }} inline-flex items-center justify-center sm:w-auto w-full sm:rounded-md border border-transparent bg-slate-900 px-3 py-3.5 sm:py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                        >
                            <x-heroicon-o-user class="w-6 h-6"/>
                        </button>
                    </a>

                    <a
                        title="Bookmarks"
                        href="{{ route('bookmarks.index') }}"
                        class="flex-1"
                        wire:navigate
                    >
                        <button
                            type="button"
                            class="{{ request()->routeIs('bookmarks.*') ? 'text-slate-100' : 'text-slate-500 hover:text-slate-100' }} inline-flex items-center justify-center sm:w-auto w-full sm:rounded-md border border-transparent bg-slate-900 px-3 py-3.5 sm:py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                        >
                            <x-heroicon-o-bookmark class="w-6 h-6"/>
                        </button>
                    </a>

                    <a
                        title="Notifications"
                        href="{{ route('notifications.index') }}"
                        class="flex-1"
                        wire:navigate
                    >
                        <button
                            type="button"
                            class="{{ request()->routeIs('notifications.index') ? 'text-slate-100' : 'text-slate-500 hover:text-slate-100' }} inline-flex items-center justify-center sm:w-auto w-full sm:rounded-md border border-transparent bg-slate-900 px-3 py-3.5 sm:py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out focus:outline-none"
                        >
                            <x-heroicon-o-bell class="w-6 h-6"/>

                            <livewire:navigation.notifications-count.show/>
                        </button>
                    </a>
                @endauth

                <x-dropdown
                    align="right"
                    width="48"
                    dropdownClasses="sm:mt-0 -mt-16 -translate-y-full sm:translate-y-2"
                >
                    <x-slot name="trigger">
                        <button
                            title="Menu"
                            class="inline-flex items-center justify-center w-full px-3 py-3.5 sm:py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out border border-transparent sm:w-auto sm:rounded-md bg-slate-900 text-slate-500 hover:text-slate-100 focus:outline-none"
                        >
                            <x-icons.bars class="size-6"/>
                        </button>
                    </x-slot>

                    <x-slot name="content">
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
                                :class="request()->routeIs('home.feed') ? 'bg-slate-800' : ''"
                            >
                                {{ __('Feed') }}
                            </x-dropdown-link>

                            <x-dropdown-link
                                :href="route('login')"
                                :class="request()->routeIs('login') ? 'bg-slate-800' : ''"
                            >
                                {{ __('Log in') }}
                            </x-dropdown-link>

                            <x-dropdown-link
                                :href="route('register')"
                                :class="request()->routeIs('register') ? 'bg-slate-800' : ''"
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
