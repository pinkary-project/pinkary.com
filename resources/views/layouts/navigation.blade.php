<nav>
    <div class="mx-auto px-4">
        <div class="flex h-16 justify-between">
            <div class="flex"></div>
            <div class="flex items-center" x-data>
                @auth
                    <a href="{{ route('home') }}" class="mr-2" wire:navigate>
                        <button
                            type="button"
                            class="{{ request()->routeIs('home') ? 'bg-gray-800 text-gray-50' : 'bg-gray-800 text-gray-400 hover:text-gray-50' }} inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none"
                        >
                            <x-icons.home class="h-6 w-6" />
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
                            <x-icons.user class="h-6 w-6" />
                        </button>
                    </a>

                    <a href="{{ route('explore') }}" class="mr-2" wire:navigate>
                        <button
                            type="button"
                            class="{{ request()->routeIs('explore') ? 'bg-gray-800 text-gray-50' : 'bg-gray-800 text-gray-400 hover:text-gray-50' }} inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none"
                        >
                            <x-icons.magnifying-glass class="h-6 w-6" />
                        </button>
                    </a>

                    <a href="{{ route('notifications.index') }}" class="mr-2" wire:navigate>
                        <button
                            type="button"
                            class="{{ request()->routeIs('notifications.index') ? 'bg-gray-800 text-gray-50' : 'bg-gray-800 text-gray-400 hover:text-gray-50' }} inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-400 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none"
                        >
                            <x-icons.bell class="h-6 w-6" />

                            <livewire:navigation.notifications-count.show />
                        </button>
                    </a>
                @endauth

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-400 transition duration-150 ease-in-out hover:text-gray-50 focus:outline-none"
                        >
                            <x-icons.bars class="h-6 w-6" />
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
