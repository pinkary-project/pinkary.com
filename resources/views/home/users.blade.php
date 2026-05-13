<x-app-layout>
    <div class="space-y-0">
        <section class="sticky top-[57px] z-30 border-b border-r border-slate-800/30 bg-[#07101f]/95 px-6 py-6 backdrop-blur lg:top-0">
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
