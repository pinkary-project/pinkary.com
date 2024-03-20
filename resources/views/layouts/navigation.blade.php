<nav>
    <div class="mx-auto px-4">
        <div class="flex h-16 justify-between">
            <div class="flex"></div>
            <div class="flex items-center" x-data>
                @if (request()->routeIs('profile.show'))
                    <div>
                        <div class="mr-2 flex items-baseline space-x-4 py-2">
                            <button
                                x-data="shareProfile"
                                x-show="isVisible"
                                @click="
                                    share({
                                        url: '{{ route('profile.show', ['user' => request()->route('user')->username]) }}'
                                    })
                                "
                                type="button"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-400 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
                            </button>
                            <button
                                x-data="copyUrl"
                                x-show="isVisible"
                                @click="copyToClipboard('{{ route('profile.show', ['user' => request()->route('user')->username]) }}')"
                                type="button"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-400 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                            </button>
                        </div>
                    </div>
                @endif

                @auth
                    <a href="{{ route('home') }}" class="mr-2" wire:navigate>
                        <button
                            type="button"
                            class="{{ request()->routeIs('home') ? 'bg-gray-800 text-gray-50' : 'bg-gray-800 text-gray-400 hover:text-gray-50' }} inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                        </button>
                    </a>

                    <a
                        href="{{ route('profile.show', ['user' => auth()->user()->username]) }}"
                        class="mr-2"
                        wire:navigate
                    >
                        <button
                            type="button"
                            class="{{ request()->fullUrlIs(route('profile.show', ['user' => auth()->user()->username])) ? 'bg-gray-800 text-gray-50' : 'bg-gray-800 text-gray-400 hover:text-gray-50' }} inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        </button>
                    </a>

                    <a href="{{ route('explore') }}" class="mr-2" wire:navigate>
                        <button
                            type="button"
                            class="{{ request()->routeIs('explore') ? 'bg-gray-800 text-gray-50' : 'bg-gray-800 text-gray-400 hover:text-gray-50' }} inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        </button>
                    </a>

                    <a href="{{ route('notifications.index') }}" class="mr-2" wire:navigate>
                        <button
                            type="button"
                            class="{{ request()->routeIs('notifications.index') ? 'bg-gray-800 text-gray-50' : 'bg-gray-800 text-gray-400 hover:text-gray-50' }} inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-400 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>

                            <livewire:navigation.notifications-count.show />
                        </button>
                    </a>
                @endauth

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-400 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" ></path></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @auth
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Settings') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <a
                                    class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-900"
                                    onclick="event.preventDefault();
                                                    this.closest('form').submit();"
                                >
                                    {{ __('Log Out') }}
                                </a>
                            </form>
                        @else
                            <x-dropdown-link :href="route('welcome')">
                                {{ __('About') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('login')">
                                {{ __('Log in') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('register')">
                                {{ __('Register') }}
                            </x-dropdown-link>
                        @endauth
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
