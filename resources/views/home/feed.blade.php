<x-app-layout>
    <section class="border-b border-r border-slate-200/70 bg-white/80 dark:border-slate-800/30 dark:bg-[#07101f]/95">
        <div class="sticky top-0 z-30 flex flex-col gap-2 border-b border-slate-200/70 bg-white px-6 py-4 dark:border-slate-800/30 dark:bg-[#07101f] sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:bg-white/90 sm:py-6 sm:backdrop-blur dark:sm:bg-[#07101f]/95">
            <h2 class="text-[2rem] font-semibold tracking-tight text-slate-950 dark:text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="border-b border-slate-200/70 px-6 py-6 dark:border-slate-800/30">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>
            @endauth

            <div>
                <livewire:home.feed />
            </div>
        </div>
    </section>
</x-app-layout>
