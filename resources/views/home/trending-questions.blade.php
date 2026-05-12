<x-app-layout>
    <section class="overflow-hidden rounded-[1.75rem] border border-slate-800/80 bg-[#07101f]/95 shadow-[0_0_0_1px_rgba(15,23,42,0.35)] ring-1 ring-white/5 backdrop-blur">
        <div class="flex flex-col gap-4 border-b border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <h2 class="text-[2.15rem] font-semibold tracking-tight text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="px-5 py-5 sm:px-6">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>
            @endauth

            <div class="px-5 pb-5 sm:px-6 sm:pb-6">
                <livewire:home.trending-questions :focus-input="true" />
            </div>
        </div>
    </section>
</x-app-layout>
