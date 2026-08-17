<x-app-layout>
    <section class="overflow-hidden border border-slate-200 bg-white lg:border-t-0 dark:border-slate-800/30 dark:bg-[#07101f]/95">
        <div class="flex flex-col gap-2 border-b border-slate-200/70 px-4 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800/30">
            <div>
                <p class="text-xs font-semibold tracking-wider text-pink-500 uppercase">Pinkary</p>
                <h2 class="mt-0.5 text-2xl font-bold tracking-tight text-slate-950 dark:text-white">
                    {{ __('Custom Feeds') }}
                </h2>
            </div>

            <x-home-menu></x-home-menu>
        </div>

        <div>
            <livewire:feeds.index />
        </div>
    </section>
</x-app-layout>
