<x-app-layout>
    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/85 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/85 dark:shadow-black/20">
        <div class="flex flex-col gap-4 border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/70 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Hashtag</p>
                <h2 class="mt-1 font-mona text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">
                    #{{ $hashtag }}
                </h2>
            </div>

            <x-home-menu></x-home-menu>
        </div>

        <div class="p-4 sm:p-6">
            <livewire:home.feed :hashtag="$hashtag" />
        </div>
    </section>
</x-app-layout>
