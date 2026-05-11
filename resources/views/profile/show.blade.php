<x-app-layout>
    <div class="space-y-6 py-4 sm:py-6">
        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-3 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-4">
            <livewire:links.index :userId="$user->id" />
        </section>

        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-3 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-4">
            <div class="mb-4 flex flex-col gap-4 rounded-[1.75rem] border border-slate-200/70 bg-slate-50/80 p-4 dark:border-slate-800/70 dark:bg-slate-900/70 sm:flex-row sm:items-end sm:justify-between sm:p-5">
                <div>
                    <div class="inline-flex items-center rounded-full border border-pink-500/20 bg-pink-500/10 px-3 py-1 text-xs font-medium uppercase tracking-[0.24em] text-pink-600 dark:border-pink-500/20 dark:bg-pink-500/10 dark:text-pink-300">
                        Profile
                    </div>

                    <h2 class="mt-4 font-mona text-2xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-3xl">
                        Ask {{ $user->name }} something.
                    </h2>
                </div>

                <p class="max-w-md text-sm leading-6 text-slate-600 dark:text-slate-400">
                    The profile feed, questions, and links all stay powered by the same existing features. This slice only reshapes the presentation.
                </p>
            </div>

            <div class="rounded-[1.75rem] border border-slate-200/70 bg-slate-50/80 p-3 dark:border-slate-800/70 dark:bg-slate-900/70 sm:p-4">
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
