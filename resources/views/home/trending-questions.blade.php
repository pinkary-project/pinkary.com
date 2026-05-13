<x-app-layout>
    <section class="overflow-hidden border-b border-r border-white/5 bg-black/10">
        <div class="flex flex-col gap-3 border-b border-white/5 px-6 py-6 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-[2rem] font-semibold tracking-tight text-white">
                Feed
            </h2>

            <x-home-menu></x-home-menu>
        </div>

        <div class="space-y-0">
            @auth
                <div class="border-b border-white/5 px-6 py-6">
                    <livewire:questions.create :toId="auth()->id()" />
                </div>
            @endauth

            <div>
                <livewire:home.trending-questions :focus-input="true" />
            </div>
        </div>
    </section>
</x-app-layout>
