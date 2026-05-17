<x-app-layout people-to-follow-context="profile" :people-to-follow-user-id="$user->id">
    <div class="space-y-0 border-x border-slate-200 py-0 dark:border-slate-700/50">
        <section class="border-b border-slate-200 bg-white/80 p-4 dark:border-slate-700/50 dark:bg-[#07101f]/95 sm:p-5">
            <livewire:links.index :userId="$user->id" />
        </section>

        <section class="border-b border-slate-200 bg-white/80 dark:border-slate-700/50 dark:bg-[#07101f]/95">
            <div class="border-b border-slate-200 px-4 py-4 dark:border-slate-700/50">
                <div class="border border-slate-200/70 bg-slate-50/80 p-4 dark:border-slate-800/30 dark:bg-[#0b1324]">
                    <livewire:questions.create :toId="$user->id" />
                </div>
            </div>

            <div>
                <livewire:questions.index
                    :userId="$user->id"
                />
            </div>
        </section>
    </div>
</x-app-layout>
