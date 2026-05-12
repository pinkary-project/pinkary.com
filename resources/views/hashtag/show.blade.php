<x-app-layout>
    <section class="overflow-hidden border border-slate-800 bg-[#07101f]/95">
        <div class="flex flex-col gap-3 border-b border-slate-800 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div>
                <p class="text-sm font-medium text-slate-400">Hashtag</p>
                <h2 class="mt-1 text-[2rem] font-semibold tracking-tight text-white">
                    #{{ $hashtag }}
                </h2>
            </div>

            <x-home-menu></x-home-menu>
        </div>

        <div class="px-4 pb-4 pt-4 sm:px-5 sm:pb-5 sm:pt-5">
            <livewire:home.feed :hashtag="$hashtag" />
        </div>
    </section>
</x-app-layout>
