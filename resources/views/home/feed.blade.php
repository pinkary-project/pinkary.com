<x-app-layout>
    <div class="space-y-6">
        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-4 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-5">
            <x-home-menu></x-home-menu>
        </section>

        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-3 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-4">
            @auth
                <div class="mb-4 rounded-[1.75rem] border border-slate-200/70 bg-slate-50/80 p-3 dark:border-slate-800/70 dark:bg-slate-900/70 sm:p-4">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>
            @endauth

            <livewire:home.feed />
        </section>
    </div>
</x-app-layout>
