<x-app-layout>
    <section class="border-x border-b border-slate-200 bg-white/80 dark:border-slate-700/50 dark:bg-[#07101f]/95">
        <div class="sticky -top-1 z-30 flex items-center justify-between border-b border-slate-200/70 bg-white px-6 py-4 sm:bg-white/90 sm:py-6 sm:backdrop-blur dark:border-slate-800/30 dark:bg-[#07101f] dark:sm:bg-[#07101f]/95">
            <h2 class="text-[2rem] font-semibold tracking-tight text-slate-950 dark:text-white">Notifications</h2>
        </div>

        <div class="min-h-screen p-4 sm:p-6">
            <livewire:notifications-index />
        </div>
    </section>
</x-app-layout>
