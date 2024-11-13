<x-main-layout backgroundImage="solid">
    <div
        x-data="toggle()"
        change class="relative mx-auto h-full max-w-7xl"
    >
        <x-sidebar
            @click.outside="closeSidebar($event)"
            x-ref="sidebar"
        />

        <div class="xl:pl-72">
            <!-- Sticky search header -->
            <div
                class="sticky top-0 z-40 flex h-16 w-full shrink-0 items-center gap-x-6 border-r border-b border-white/5 bg-gray-900 px-6 shadow-sm xl:px-8">
                <button
                    @click="toggleSidebar($event)"
                    class="text-white -m-2.5 p-2.5 xl:hidden">
                    <span class="sr-only">Open sidebar</span>
                    <x-icons.menu class="size-5"/>
                </button>

                <div class="flex flex-1 gap-x-4 self-stretch xl:gap-x-6">
                    <form class="flex flex-1" action="{{ route('home.users') }}" method="GET">
                        @csrf
                        <label for="search-field" class="sr-only">Search</label>
                        <div class="relative w-full">
                            <x-icons.magnifying-glass class="absolute inset-y-0 left-0 h-full w-5 text-gray-500"/>

                            <input id="search-field"
                                   class="block h-full w-full border-0 bg-transparent py-0 pr-0 pl-8 text-white focus:ring-0 xl:text-sm"
                                   placeholder="Search for users or hashtags..." type="search" name="search">
                        </div>
                    </form>
                </div>
            </div>

            <div class="h-full xl:grid xl:grid-cols-3">
                <main class="col-span-2 w-full xl:max-w-2xl">
                    {{ $slot }}
                </main>

                <aside class="border-t border-white/5 bg-black/10 xl:flex-1 xl:border-x xl:border-t-0">
                    <div class="xl:sticky xl:top-16 xl:right-0 xl:bottom-0">
                        <div class="flex flex-col border-b border-white/5 p-6 text-sm text-gray-400 xl:p-8">
                            <span class="mb-1 text-[10px]">PUB</span>
                            <img src="https://forge.laravel.com/social-share.png" class="rounded-md">
                            <p class="mt-2">Server management doesn't have to be a nightmare.</p>
                        </div>

                        <div class="flex items-center justify-between border-b border-white/5 p-6 xl:p-8">
                            <h2 class="text-sm font-medium text-white">Recent <span class="opacity-50">signups</span>
                            </h2>
                            <a href="" class="text-xs font-medium text-pink-500">View all</a>
                        </div>

                        <ul role="list" class="divide-y divide-white/5">
                            <li class="cursor-pointer px-6 py-4 hover:bg-gray-800/20">
                                <div class="flex items-center gap-x-3">
                                    <img
                                        src="https://pinkary.com/storage/avatars/120f8d175fd0146ca0541625b8bd6c742e838632951a7e58dc7fbdc8c2170c4f.png"
                                        alt="" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                                    <h3 class="flex-auto truncate text-sm text-white">Nuno Maduro</h3>
                                    <time datetime="2023-01-23T11:00" class="flex-none text-xs text-gray-600">1h</time>
                                </div>
                            </li>
                            <li class="cursor-pointer px-6 py-4 hover:bg-gray-800/20">
                                <div class="flex items-center gap-x-3">
                                    <img
                                        src="https://pinkary.com/storage/avatars/d6518d7d630379e204df6891b25617f1495cfecf11557526d3ee054d5db33704.png"
                                        alt="" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                                    <h3 class="flex-auto truncate text-sm text-white">Cam Kemshal-Bell</h3>
                                    <time datetime="2023-01-23T11:00" class="flex-none text-xs text-gray-600">2h</time>
                                </div>
                            </li>
                            <li class="cursor-pointer px-6 py-4 hover:bg-gray-800/20">
                                <div class="flex items-center gap-x-3">
                                    <img
                                        src="https://pinkary.com/storage/avatars/0c64f67f3f182570b8cf1028e5a72fa0ac83b2c295c6bf06e8ea930a89026f15.png"
                                        alt="" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                                    <h3 class="flex-auto truncate text-sm text-white">Punyapal Shah</h3>
                                    <time datetime="2023-01-23T11:00" class="flex-none text-xs text-gray-600">2h</time>
                                </div>
                            </li>
                            <li class="cursor-pointer px-6 py-4 hover:bg-gray-800/20">
                                <div class="flex items-center gap-x-3">
                                    <img
                                        src="https://pinkary.com/storage/avatars/54b5512e2676bf1900aafe92a707d8941773128bbec6a05ac6e2d3adf3160bf1.png"
                                        alt="" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                                    <h3 class="flex-auto truncate text-sm text-white">Joel Clermont</h3>
                                    <time datetime="2023-01-23T11:00" class="flex-none text-xs text-gray-600">3h</time>
                                </div>
                            </li>
                            <li class="cursor-pointer px-6 py-4 hover:bg-gray-800/20">
                                <div class="flex items-center gap-x-3">
                                    <img
                                        src="https://pinkary.com/storage/avatars/bec85860bae95878772479fb9febce97b67444f7aa3dcbf0129958d43886f5ac.png"
                                        alt="" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                                    <h3 class="flex-auto truncate text-sm text-white">Nuno Guerra</h3>
                                    <time datetime="2023-01-23T11:00" class="flex-none text-xs text-gray-600">4h</time>
                                </div>
                            </li>
                        </ul>

                        <div class="relative flex flex-col border-t border-white/5 p-6 xl:py-8">
                            <span class="text-sm font-medium text-gray-200">Pinkary</span>
                            <span class="text-xs text-gray-500">One link. All your socials.</span>

                            <ul class="mt-3 flex flex-wrap gap-x-4 text-xs">
                                <li class="flex">
                                    <a class="text-gray-500 transition hover:text-gray-200 py-2" href="{{ route('about') }}">{{ __('About') }}</a>
                                </li>
                                <li class="flex">
                                    <a class="text-gray-500 transition hover:text-gray-200 py-2" href="/advertise">{{ __('Advertise') }}</a>
                                </li>
                                <li class="flex">
                                    <a class="text-gray-500 transition hover:text-gray-200 py-2" href="{{ route('terms') }}">{{ __('Terms') }}</a>
                                </li>
                                <li class="flex">
                                    <a class="text-gray-500 transition hover:text-gray-200 py-2" href="{{ route('privacy') }}">{{ __('Privacy') }}</a>
                                </li>
                                <li class="flex">
                                    <a class="text-gray-500 transition hover:text-gray-200 py-2" href="{{ route('support') }}">{{ __('Support') }}</a>
                                </li>
                                <li class="flex">
                                    <a class="text-gray-500 transition hover:text-gray-200 py-2" href="{{ route('verified') }}">{{ __('Verified') }}</a>
                                </li>
                                <li class="flex">
                                    <a class="text-gray-500 transition hover:text-gray-200 py-2" href="{{ route('brand.resources') }}">{{ __('Brand') }}</a>
                                </li>
                                <li class="flex">
                                    <a class="text-gray-500 transition hover:text-gray-200 py-2" href="{{ route('changelog') }}">{{ __('Changelog') }}</a>
                                </li>
                            </ul>

                            <div class="mt-4 flex gap-3">
                                <a href="https://x.com/pinkaryproject" target="_blank" rel="noreferrer noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                         class="text-gray-500 transition size-5 hover:text-gray-200" fill="none">
                                        <path
                                            d="M7 17L11.1935 12.8065M17 7L12.8065 11.1935M12.8065 11.1935L9.77778 7H7L11.1935 12.8065M12.8065 11.1935L17 17H14.2222L11.1935 12.8065"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                        <path
                                            d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                                            stroke="currentColor" stroke-width="1.5"></path>
                                    </svg>
                                </a>

                                <a href="https://github.com/pinkary-project/pinkary.com/" target="_blank" rel="noreferrer noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                         class="text-gray-500 transition size-5 hover:text-gray-200" fill="none">
                                        <path
                                            d="M6.51734 17.1132C6.91177 17.6905 8.10883 18.9228 9.74168 19.2333M9.86428 22C8.83582 21.8306 2 19.6057 2 12.0926C2 5.06329 8.0019 2 12.0008 2C15.9996 2 22 5.06329 22 12.0926C22 19.6057 15.1642 21.8306 14.1357 22C14.1357 22 13.9267 18.5826 14.0487 17.9969C14.1706 17.4113 13.7552 16.4688 13.7552 16.4688C14.7262 16.1055 16.2043 15.5847 16.7001 14.1874C17.0848 13.1032 17.3268 11.5288 16.2508 10.0489C16.2508 10.0489 16.5318 7.65809 15.9996 7.56548C15.4675 7.47287 13.8998 8.51192 13.8998 8.51192C13.4432 8.38248 12.4243 8.13476 12.0018 8.17939C11.5792 8.13476 10.5568 8.38248 10.1002 8.51192C10.1002 8.51192 8.53249 7.47287 8.00036 7.56548C7.46823 7.65809 7.74917 10.0489 7.74917 10.0489C6.67316 11.5288 6.91516 13.1032 7.2999 14.1874C7.79575 15.5847 9.27384 16.1055 10.2448 16.4688C10.2448 16.4688 9.82944 17.4113 9.95135 17.9969C10.0733 18.5826 9.86428 22 9.86428 22Z"
                                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-main-layout>
