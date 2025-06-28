<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.components.head')
</head>

<body
    class="font-['inter'] dark:bg-slate-950 bg-slate-100 bg-center bg-repeat dark:text-slate-50 text-slate-900 antialiased"
    style="background-image: url({{ asset('/img/dots.svg') }})">
    <livewire:flash-messages.show />

    {{-- <div class="flex min-h-screen flex-col">
        <main class="flex-grow">
            <div class="fixed right-0 z-50">
                @if (!request()->routeIs('about'))
                    @include('layouts.navigation')
                @endif
            </div>

            <div>
                <a href="{{ route('home.feed') }}" wire:navigate class="mt-20 flex justify-center">
                    <h2 class="text-3xl font-['poppins'] font-semibold">batuly</h2>
                </a>

                <div class="mx-auto w-full max-w-md px-4 py-10 sm:px-0">
                    {{ $slot }}
                </div>
            </div>
        </main>

        <x-footer />
        <div class="grid grid-cols-2">
            <div class="p-10 bg-white h-[100vh] px-24 flex flex-col">
                <h2 class="font-['poppins'] font-bold text-xl">batuly</h2>
                <div class="flex-grow flex items-center">
                    {{ $slot }}
                </div>
            </div>
            <div class="p-10"></div>
        </div>
    </div> --}}
    {{ $slot }}
    @livewireScriptConfig
</body>

</html>
