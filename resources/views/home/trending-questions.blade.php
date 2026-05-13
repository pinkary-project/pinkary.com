<x-app-layout>
    <section class="overflow-hidden border-b border-r border-slate-800/30 bg-[#07101f]/95">
        <div class="sticky top-[57px] z-30 flex flex-col gap-3 border-b border-slate-800/30 bg-[#07101f]/95 px-6 py-6 backdrop-blur sm:flex-row sm:items-center sm:justify-between lg:top-0">
            <h2 class="text-[2rem] font-semibold tracking-tight text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="border-b border-slate-800/30 px-6 py-6">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>
            @endauth

            <div>
                <livewire:home.trending-questions :focus-input="true" />
            </div>
        </div>
    </section>
</x-app-layout>
