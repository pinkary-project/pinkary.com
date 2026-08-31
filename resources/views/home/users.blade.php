<x-app-layout>
    <div class="space-y-0">
        <section class="sticky top-[57px] z-30 border-r border-b border-slate-200/70 bg-white/90 px-4 py-4 backdrop-blur lg:top-0 dark:border-slate-800/30 dark:bg-[#07101f]/95">
            <div class="space-y-3">
                <h2 class="text-[2rem] font-semibold tracking-tight text-slate-950 sm:text-[2.1rem] dark:text-white">
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
