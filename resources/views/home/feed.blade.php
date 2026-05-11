<x-app-layout>
    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/85 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/85 dark:shadow-black/20">
        <div class="flex flex-col gap-4 border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/70 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <h2 class="font-mona text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-4 p-4 sm:p-6">
            @auth
                <livewire:questions.create :toId="auth()->id()" />
            @endauth

            <livewire:home.feed />
        </div>
    </section>
</x-app-layout>
