<x-app-layout>
    <section class="overflow-hidden border border-slate-800/40 lg:border-t-0 bg-[#07101f]/95">
        <div class="flex flex-col gap-2 border-b border-slate-800/40 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between sm:px-4">
            <h2 class="text-[2rem] font-semibold tracking-tight text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="px-0 py-2">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>
            @endauth

            <div class="pb-1.5 sm:pb-2">
                <livewire:home.feed />
            </div>
        </div>
    </section>
</x-app-layout>
