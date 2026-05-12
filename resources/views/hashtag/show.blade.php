<x-app-layout>
    <section class="overflow-hidden rounded-[2.25rem] border border-slate-800/80 bg-[#07101f]/95 shadow-[0_0_0_1px_rgba(15,23,42,0.35)] ring-1 ring-white/5 backdrop-blur">
        <div class="flex flex-col gap-4 border-b border-slate-800 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <p class="text-sm font-medium text-slate-400">Hashtag</p>
                <h2 class="mt-1 text-[2.15rem] font-semibold tracking-tight text-white">
                    #{{ $hashtag }}
                </h2>
            </div>

            <x-home-menu></x-home-menu>
        </div>

        <div class="p-5 sm:p-6">
            <livewire:home.feed :hashtag="$hashtag" />
        </div>
    </section>
</x-app-layout>
