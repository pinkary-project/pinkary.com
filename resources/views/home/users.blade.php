<x-app-layout>
    <div class="space-y-4">
        <section class="border border-slate-800 lg:border-t-0 bg-[#07101f]/95 px-3 py-2.5 sm:px-4">
            <div class="space-y-3">
                <h2 class="text-[2rem] font-semibold tracking-tight text-white sm:text-[2.1rem]">
                    Search
                </h2>

                <x-home-menu></x-home-menu>
            </div>
        </section>

        <section class="min-h-screen">
            <livewire:home.users :focus-input="true" />
        </section>
    </div>
</x-app-layout>
