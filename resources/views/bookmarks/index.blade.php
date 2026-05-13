<x-app-layout>
    <x-slot name="title">Bookmarks</x-slot>

    <section class="border-b border-r border-slate-200/70 bg-white/80 px-6 py-6 dark:border-slate-800/30 dark:bg-[#07101f]/95">
        <div class="mx-auto min-h-screen w-full max-w-[44rem] overflow-hidden">
            <livewire:bookmarks.index />
        </div>
    </section>
</x-app-layout>
