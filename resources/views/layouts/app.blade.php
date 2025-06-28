<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.components.head')
</head>

<body class="font-['inter']  bg-slate-100 bg-center bg-repeat text-slate-950 antialiased"
    style="background-image: url({{ asset('/img/dots.svg') }})">
    @persist('flash-messages')
        <livewire:flash-messages.show />
    @endpersist
    <div class="md:grid md:grid-cols-4 md:px-44">
        <div class="h-fit sticky top-10 hidden md:block">
            <a wire:navigate href="{{ route('home.feed') }}">
                <h2 class="font-bold font-['poppins'] text-2xl">batuly</h2>
            </a>
            <div class="mt-10 grid space-y-5">
                <a href="{{ route('home.feed') }}" wire:navigate
                    class="{{ request()->routeIs('home.feed') ? 'font-semibold' : '' }} inline-flex items-center gap-2"><x-heroicon-o-home
                        class="h-6 w-6" /> Home</a>
                <a href="{{ route('bookmarks.index') }}" wire:navigate
                    class="{{ request()->routeIs('bookmarks.index') ? 'font-semibold' : '' }} inline-flex items-center gap-2"><x-heroicon-o-bookmark
                        class="h-6 w-6" />
                    Bookmarks</a>
                <a href="{{ route('notifications.index') }}" wire:navigate
                    class="{{ request()->routeIs('notifications.index') ? 'font-semibold' : '' }} inline-flex items-center gap-2"><x-heroicon-o-bell
                        class="h-6 w-6" />
                    Notifications</a>
            </div>
            @if (auth()->user())
                <div
                    class="relative flex cursor-pointer rounded-full justify-between items-center group mt-10 hover:bg-white duration-300">
                    <a href="{{ route('profile.show', ['username' => auth()->user()->username]) }}"
                        class="block w-full p-2">
                        <div class="flex gap-2 items-center">
                            <div class="size-10">
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->username }}"
                                    class="{{ auth()->user()->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-full w-full object-contain cursor-pointer"
                                    x-on:click="showAvatar = true" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold duration-300">
                                    {{ auth()->user()->name }}</h2>
                                <h3 class="font-medium text-gray-700 text-xs">@
                                    {{ auth()->user()->username }}</h3>
                            </div>
                        </div>
                    </a>
                    <button
                        class="btn size-10 rounded-full absolute border flex justify-center items-center hover:bg-black hover:text-white right-5 duration-300">
                        <x-heroicon-o-arrow-right-end-on-rectangle class="h-5 w-5" />
                    </button>
                </div>
            @endif
        </div>
        <div class="col-span-2 pt-10">
            {{-- @include('layouts.navigation') --}}
            <div>
                {{ $slot }}
            </div>
        </div>
        <div class="hidden md:block h-fit sticky top-10 mt-10">
            <livewire:trending-tags />
            <div class="mt-10">
                <div class="p-5 border rounded-xl space-y-2 bg-white">
                    <div class="flex justify-between items-center">
                        <h2 class="font-semibold">Get Started to Batuly</h2>
                        <x-heroicon-o-sparkles class="h-5 w-5 text-blue-500" />
                    </div>
                    <p class="text-sm text-gray-700">Smart, Simple, Powerful – The Only Business Assistant You’ll Ever
                        Need</p>
                    <div>
                        <a href="https://batuly.com" target="_blank">
                            <button
                                class="btn text-sm bg-blue-500 px-3 py-2 hover:bg-blue-600 text-white rounded-md">Get
                                Started</button></a>
                    </div>
                </div>
            </div>
            <div class="mt-10 text-xs text-gray-700 divide-black divide-x">
                <a href="#" class="pr-2">Terms of Service</a>
                <a href="#" class="pl-2">Privacy Policy</a>
            </div>
        </div>
    </div>
    {{-- <div class="flex min-h-screen flex-col">
            <div class="mx-auto ml-3 mr-3 flex-grow">
                @include('layouts.navigation')
                @if (isset($title))
                    <div class="mb-6 mt-20 flex flex-col items-center sm:mb-12">
                        <div class="w-full max-w-md px-2 sm:px-0">
                            <h1 class="font-mona text-2xl font-medium dark:text-slate-200 text-slate-900">
                                {{ $title }}
                            </h1>
                        </div>
                    </div>
                @endif
                <main class="mt-16">
                    {{ $slot }}
                </main>
                <x-image-lightbox />
            </div>

            @persist('footer')
                <x-back-to-top :offset="300" />
                <x-footer />
            @endpersist
        </div> --}}
    @livewireScriptConfig

    <script>
        window.onload = function() {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone
            if (timezone !== '{{ session()->get('timezone', 'UTC') }}') {
                axios.post('{{ route('profile.timezone.update') }}', {
                    timezone
                })
            }

            Livewire.hook('request', ({
                uri,
                options,
                payload,
                respond,
                succeed,
                fail
            }) => {
                fail(({
                    status,
                    content,
                    preventDefault
                }) => {
                    if (status === 419) {
                        preventDefault()
                    }
                })
            })
        }
    </script>
</body>

</html>
