<x-app-layout>
    <div class="space-y-6">
        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-4 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                        Search
                    </div>

                    <h2 class="mt-4 font-mona text-2xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-3xl">
                        Discover people already on Pinkary.
                    </h2>
                </div>

                <p class="max-w-md text-sm leading-6 text-slate-600 dark:text-slate-400">
                    The people directory and follow interactions stay the same. This pass only reshapes the browse experience.
                </p>
            </div>

            <div class="mt-6">
                <x-home-menu></x-home-menu>
            </div>
        </section>

        <section class="min-h-screen rounded-[2rem] border border-white/70 bg-white/85 p-3 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-4">
            <livewire:home.users :focus-input="true" />
        </section>
    </div>
</x-app-layout>
