<x-app-layout>
    <div class="space-y-6">
        <section class="rounded-[2.25rem] border border-slate-800/80 bg-[#07101f]/95 p-5 shadow-[0_0_0_1px_rgba(15,23,42,0.35)] ring-1 ring-white/5 backdrop-blur sm:p-6">
            <div class="space-y-4">
                <h2 class="text-[2.15rem] font-semibold tracking-tight text-white sm:text-[2.3rem]">
                    Search
                </h2>

                <x-home-menu></x-home-menu>
            </div>
        </section>

        <section class="min-h-screen">
            <livewire:home.users :focus-input="true" />
        </section>
    </div>
</x-app-layout>
