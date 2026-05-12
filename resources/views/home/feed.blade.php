<x-app-layout>
    <section class="overflow-hidden border border-slate-800 bg-[#07101f]/95">
        <div class="flex flex-col gap-3 border-b border-slate-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <h2 class="text-[2rem] font-semibold tracking-tight text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="px-4 py-4 sm:px-5">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>
            @endauth

            <div class="px-4 pb-4 sm:px-5 sm:pb-5">
                <livewire:home.feed />
            </div>
        </div>
    </section>
</x-app-layout>
