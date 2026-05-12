<x-app-layout>
    <section class="overflow-hidden border border-slate-800/40 lg:border-t-0 bg-[#07101f]/95">
        <div class="flex flex-col gap-2 border-b border-slate-800/40 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between sm:px-4">
            <div>
                <p class="text-sm font-medium text-slate-400">Hashtag</p>
                <h2 class="mt-1 text-[2rem] font-semibold tracking-tight text-white">
                    #{{ $hashtag }}
                </h2>
            </div>

            <x-home-menu></x-home-menu>
        </div>

        <div class="pb-1.5 pt-1.5 sm:pb-2 sm:pt-2">
            <livewire:home.feed :hashtag="$hashtag" />
        </div>
    </section>
</x-app-layout>
