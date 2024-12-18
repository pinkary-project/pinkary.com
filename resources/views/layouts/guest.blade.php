<x-main-layout backgroundImage="dots">
        <div class="flex min-h-screen flex-col">
            <main class="flex-grow">
                <div class="fixed right-0 z-50">
                    @include('layouts.navigation')
                </div>

                <div>
                    <a
                        href="{{ route('home.feed') }}"
                        wire:navigate
                        class="mt-20 flex justify-center"
                    >
                        <x-pinkary-logo class="z-10 w-48" />
                    </a>

                    <div class="mx-auto w-full max-w-md px-4 py-10 sm:px-0">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            <x-footer />
        </div>
</x-main-layout>
