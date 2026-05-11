<x-app-layout>
    <div class="space-y-6">
        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-4 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-5">
            <div class="space-y-4">
                <h2 class="font-mona text-2xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-3xl">
                    Search
                </h2>

                <x-home-menu></x-home-menu>
            </div>
        </section>

        <section class="min-h-screen rounded-[2rem] border border-white/70 bg-white/85 p-3 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-4">
            <livewire:home.users :focus-input="true" />
        </section>
    </div>
</x-app-layout>
