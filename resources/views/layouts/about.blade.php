<x-main-layout backgroundImage="dots">
        <div class="flex min-h-screen flex-col">
            <main class="flex-grow">
                <div class="flex min-h-screen flex-col justify-center overflow-hidden">
                    {{ $slot }}
                </div>
            </main>

            <x-footer />
        </div>
</x-main-layout>
