<x-app-layout>
    <section class="overflow-hidden border border-slate-800 lg:border-t-0 bg-[#07101f]/95">
        <div class="flex flex-col gap-2 border-b border-slate-800 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between sm:px-4">
            <h2 class="text-[2rem] font-semibold tracking-tight text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="px-3 py-3 sm:px-4">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>
            @endauth

            <div class="px-3 pb-3 sm:px-4 sm:pb-4">
                <livewire:home.trending-questions :focus-input="true" />
            </div>
        </div>
    </section>
</x-app-layout>
