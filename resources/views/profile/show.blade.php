<x-app-layout>
    <div class="space-y-0 py-0">
        <section class="border-b border-r border-slate-200/70 bg-white/80 p-4 dark:border-slate-800/30 dark:bg-[#07101f]/95 sm:p-5">
            <livewire:links.index :userId="$user->id" />
        </section>

        <section class="border-b border-r border-slate-200/70 bg-white/80 p-4 dark:border-slate-800/30 dark:bg-[#07101f]/95 sm:p-5">
            <div class="border border-slate-200/70 bg-slate-50/80 p-4 dark:border-slate-800/30 dark:bg-[#0b1324]">
                <livewire:questions.create :toId="$user->id" />
            </div>

            <div class="mt-4">
                <livewire:questions.index
                    :userId="$user->id"
                />
            </div>
        </section>
    </div>
</x-app-layout>
