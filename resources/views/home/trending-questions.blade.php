<x-app-layout>
    <section class="overflow-hidden border border-slate-800/55 lg:border-t-0 bg-[#07101f]/95">
        <div class="flex flex-col gap-2 border-b border-slate-800/55 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between sm:px-4">
            <h2 class="text-[2rem] font-semibold tracking-tight text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="px-2 py-2.5 sm:px-3">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>
            @endauth

            <div class="px-0 pb-2 sm:pb-3">
                <livewire:home.trending-questions :focus-input="true" />
            </div>
        </div>
    </section>
</x-app-layout>
