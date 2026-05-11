<x-app-layout>
    <div class="space-y-6 py-4 sm:py-6"
         x-data
         x-init="document.getElementById('q-{{ $question->id }}').scrollIntoView();">
        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-4 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <a
                    x-data="{
                     fallback: '{{ session('_previous.url', route('profile.show', ['username' => $question->to->username]))  }}',

                     back: function() {
                            if (history.length > 1) {
                                history.back();
                            } else {
                                window.location.href = this.fallback;
                            }
                        }
                     }"
                    x-on:click.prevent="back()"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200/70 bg-slate-50/80 px-4 py-2 text-sm font-medium text-slate-500 transition hover:bg-white hover:text-slate-950 dark:border-slate-800/70 dark:bg-slate-900/70 dark:text-slate-400 dark:hover:bg-slate-950 dark:hover:text-white cursor-pointer"
                >
                    <x-icons.chevron-left class="h-5 w-5" />
                    <span>Back</span>
                </a>

                <div class="max-w-md text-sm leading-6 text-slate-600 dark:text-slate-400">
                    Follow the full thread, jump back through parent posts, and continue the conversation with the existing reply flow.
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-3 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800/80 dark:bg-slate-950/80 dark:shadow-black/20 sm:p-4">
            @foreach($parentQuestions as $parentQuestion)
                <livewire:questions.show :questionId="$parentQuestion->id" :in-thread="true" :key="$parentQuestion->id" />
                <x-post-divider />
            @endforeach

            <livewire:questions.show :questionId="$question->id" :in-thread="true" :commenting="true" />

            <div class="mt-4 rounded-[1.75rem] border border-slate-200/70 bg-slate-50/80 p-3 dark:border-slate-800/70 dark:bg-slate-900/70 sm:p-4">
                <x-comments :question="$question" />
            </div>
        </section>
    </div>
</x-app-layout>
