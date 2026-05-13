<x-app-layout>
    <div class="space-y-0 py-0">
        <section class="overflow-hidden border-b border-r border-slate-800/30 bg-[#07101f]/95 p-4 sm:p-6">
            <livewire:links.index :userId="$user->id" />
        </section>

        <section class="overflow-hidden border-b border-r border-slate-800/30 bg-[#07101f]/95 p-4 sm:p-6">
            <div class="mb-4 border border-slate-800/30 bg-[#0b1324] p-4 sm:p-5">
                <div>
                    <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                        Profile
                    </div>

                    <h2 class="mt-4 font-mona text-2xl font-semibold tracking-tight text-white sm:text-3xl">
                        Ask {{ $user->name }} something.
                    </h2>
                </div>
            </div>

            <div class="border border-slate-800/30 bg-[#0b1324] p-4">
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
