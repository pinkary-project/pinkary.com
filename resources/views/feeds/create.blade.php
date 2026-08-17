<x-app-layout>
    <section class="overflow-hidden border border-slate-200 bg-white lg:border-t-0 dark:border-slate-800/30 dark:bg-[#07101f]/95">
        <div class="flex items-center justify-between border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/30">
            <div>
                <p class="text-xs font-semibold tracking-wider text-pink-500 uppercase">Pinkary</p>
                <h2 class="mt-0.5 text-2xl font-bold tracking-tight text-slate-950 dark:text-white">
                    {{ __('Create Custom Feed') }}
                </h2>
            </div>
        </div>

        <div>
            <livewire:feeds.create />
        </div>
    </section>
</x-app-layout>
