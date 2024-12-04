<div {{ $attributes->class(['fixed xl:inset-y-0 top-16 bottom-0 z-50 w-72 flex-col hidden bg-black/10']) }}>
    <div class="flex grow flex-col gap-y-5 overflow-y-auto border-x border-white/5 px-6">
        <div class="mt-6 flex shrink-0">
            <a href="{{ route('about') }}" class="flex items">
                <x-pinkary-logo class="h-12"/>
            </a>
        </div>

        <nav class="flex flex-1 flex-col">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                <li>
                    <ul role="list" class="-mx-2 space-y-1">
                        <x-sidebar-link
                            active="{{ request()->routeIs('home.*') }}"
                            href="{{ route('home.feed') }}"
                            title="{{ __('Home') }}"
                        >
                            <svg
                                class="h-6 w-6 shrink-0"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M3.16405 11.3497L4 11.5587L4.45686 16.1005C4.715 18.6668 4.84407 19.9499 5.701 20.7249C6.55793 21.5 7.84753 21.5 10.4267 21.5H13.5733C16.1525 21.5 17.4421 21.5 18.299 20.7249C19.1559 19.9499 19.285 18.6668 19.5431 16.1005L20 11.5587L20.836 11.3497C21.5201 11.1787 22 10.564 22 9.85882C22 9.35735 21.7553 8.88742 21.3445 8.59985L13.1469 2.86154C12.4583 2.37949 11.5417 2.37949 10.8531 2.86154L2.65549 8.59985C2.24467 8.88742 2 9.35735 2 9.85882C2 10.564 2.47993 11.1787 3.16405 11.3497Z"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                                <path d="M15 16C14.2005 16.6224 13.1502 17 12 17C10.8498 17 9.79952 16.6224 9 16"
                                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                            {{ __('Home') }}
                        </x-sidebar-link>
                        @auth
                            <x-sidebar-link
                                active="{{ request()->routeIs('profile.*') }}"
                                href="{{ route('profile.show', auth()->user()->username) }}"
                                title="{{ __('Profile') }}"
                            >
                                <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                     fill="none">
                                    <path
                                        d="M7.78256 17.1112C6.68218 17.743 3.79706 19.0331 5.55429 20.6474C6.41269 21.436 7.36872 22 8.57068 22H15.4293C16.6313 22 17.5873 21.436 18.4457 20.6474C20.2029 19.0331 17.3178 17.743 16.2174 17.1112C13.6371 15.6296 10.3629 15.6296 7.78256 17.1112Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path
                                        d="M15.5 10C15.5 11.933 13.933 13.5 12 13.5C10.067 13.5 8.5 11.933 8.5 10C8.5 8.067 10.067 6.5 12 6.5C13.933 6.5 15.5 8.067 15.5 10Z"
                                        stroke="currentColor" stroke-width="1.5"></path>
                                    <path
                                        d="M2.854 16C2.30501 14.7664 2 13.401 2 11.9646C2 6.46129 6.47715 2 12 2C17.5228 2 22 6.46129 22 11.9646C22 13.401 21.695 14.7664 21.146 16"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                </svg>
                                {{ __('Profile') }}
                            </x-sidebar-link>
                            <x-sidebar-link
                                active="{{ request()->routeIs('notifications.*') }}"
                                href="{{ route('notifications.index') }}"
                                title="{{ __('Notifications') }}"
                            >
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex items-center gap-x-3">
                                        <x-icons.notifications class="shrink-0 size-6"/>
                                        {{ __('Notifications') }}
                                    </div>
                                    <livewire:navigation.notifications-count.show/>
                                </div>
                            </x-sidebar-link>
                            <x-sidebar-link
                                active="{{ request()->routeIs('bookmarks.*') }}"
                                href="{{ route('bookmarks.index') }}"
                                title="{{ __('Bookmarks') }}"
                            >
                                <x-heroicon-o-bookmark class="shrink-0 size-6"/>
                                {{ __('Bookmarks') }}
                            </x-sidebar-link>
                            <x-sidebar-link
                                active="{{ request()->routeIs('profile.edit.*') }}"
                                href="{{ route('profile.edit') }}"
                            >
                                <x-icons.settings class="shrink-0 size-6"/>
                                {{ __('Settings') }}
                            </x-sidebar-link>
                        @endauth
                        @guest
                            <x-sidebar-link
                                active="{{ request()->routeIs('login') }}"
                                href="{{ route('login') }}"
                                title="{{ __('Login') }}"
                            >
                                <x-icons.login class="shrink-0 size-6"/>
                                {{ __('Login') }}
                            </x-sidebar-link>
                            <x-sidebar-link
                                active="{{ request()->routeIs('register') }}"
                                href="{{ route('register') }}"
                                title="{{ __('Register') }}"
                            >
                                <x-icons.register class="shrink-0 size-6"/>
                                {{ __('Register') }}
                            </x-sidebar-link>
                        @endguest
                    </ul>
                </li>
                <li>
                    <div class="font-semibold text-gray-400 text-xs/6">Your Pinkaries</div>
                    <ul role="list" class="-mx-2 mt-2 space-y-1">
                        <li>
                            <a href=""
                               class="flex items-center justify-between gap-x-3 rounded-md p-2 font-semibold text-gray-400 group text-sm/6 hover:bg-gray-800 hover:text-white">
                                <div class="flex items-center gap-x-3">
                                    <img
                                        src="https://pinkary.com/storage/avatars/bec85860bae95878772479fb9febce97b67444f7aa3dcbf0129958d43886f5ac.png"
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg">
                                    <span class="truncate">Nuno Guerra</span>
                                </div>
                                <span class="rounded-full bg-pink-500 size-2"></span>
                            </a>
                        </li>
                        <li>
                            <a href=""
                               class="flex gap-x-3 rounded-md p-2 font-semibold text-gray-400 group text-sm/6 hover:bg-gray-800 hover:text-white">
                                <div class="flex h-6 w-6 overflow-hidden rounded-lg"><img
                                        src="https://pbs.twimg.com/profile_images/1442917630562619392/HppE-Ki3_400x400.jpg"
                                        class="shrink-0 scale-125 rounded-lg object-cover"></div>
                                <span class="truncate">Indústria Criativa</span>
                            </a>
                        </li>
                        <li>
                            <a href=""
                               class="flex gap-x-3 rounded-md p-2 font-semibold text-gray-400 group text-sm/6 hover:bg-gray-800 hover:text-white">
                                <img
                                    src="https://pbs.twimg.com/profile_images/3745095252/e60bea7df60f322ff9dd0fb95402df0d_400x400.png"
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg">
                                <span class="truncate">Yeah Works</span>
                            </a>
                        </li>
                        <li>
                            <a href=""
                               class="flex gap-x-3 rounded-md p-2 font-semibold text-gray-400 group text-sm/6 hover:bg-gray-800 hover:text-white">
                                <img src="https://condeasy.test/imgs/favicon.svg"
                                     class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-green-100 py-1 pl-px">
                                <span class="truncate">Condeasy</span>
                            </a>
                        </li>
                        <li>
                            <a href=""
                               class="flex gap-x-3 rounded-md p-2 font-semibold text-gray-400 group text-sm/6 hover:bg-gray-800 hover:text-white">
                                <img src="https://pbs.twimg.com/profile_images/1790730660929277952/xHSZAPqr_400x400.jpg"
                                     class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg">
                                <span class="truncate">Eufaturo</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="-mx-6 mt-auto">
                    @auth
                    <div class="flex items-center justify-between gap-x-4 px-6 py-3 font-semibold text-white text-sm/6 hover:bg-gray-800">
                        <div class="flex items-center gap-x-4">
                            <img class="h-8 w-8 rounded-full bg-gray-800"
                                 src="{{ auth()->user()->avatar }}"
                                 alt="{{ auth()->user()->username }}">
                            <span aria-hidden="true">{{ auth()->user()->username }}</span>
                        </div>

                        <x-dropdown
                            align="left"
                            width="48"
                            class="absolute right-0"
                        >
                            <x-slot:trigger>
                                <x-heroicon-o-ellipsis-horizontal class="size-6 cursor-pointer"/>
                            </x-slot>
                            <x-slot:content>
                                <x-dropdown-button
                                    wire:navigate="route('logout')"
                                    class="flex items-center gap-1.5"
                                    >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none">
                                        <path d="M2.5 12C2.5 7.52163 2.5 5.28245 3.89124 3.89121C5.28249 2.49997 7.52166 2.49997 12 2.49997C16.4783 2.49997 18.7175 2.49997 20.1088 3.89121C21.5 5.28245 21.5 7.52163 21.5 12C21.5 16.4783 21.5 18.7175 20.1088 20.1087C18.7175 21.5 16.4783 21.5 12 21.5C7.52166 21.5 5.28249 21.5 3.89124 20.1087C2.5 18.7175 2.5 16.4783 2.5 12Z" stroke="currentColor" stroke-width="1.5" />
                                        <path d="M7.03662 12.0275L14.0122 12.0275M14.0122 12.0275C14.0122 12.5979 11.857 14.5148 11.857 14.5148M14.0122 12.0275C14.0122 11.4421 11.857 9.56307 11.857 9.56307M17.0366 7.99509V15.9951" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                    Logout
                                </x-dropdown-button>
                            </x-slot:content>
                        </x-dropdown>
                    </div>
                    @endauth
                </li>
            </ul>
        </nav>
    </div>
</div>
