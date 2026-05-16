<x-app-layout>
    <div class="py-0"
         x-data
         x-init="document.getElementById('q-{{ $question->id }}').scrollIntoView();">
        <section class="overflow-hidden border-x border-b border-slate-200 bg-white dark:border-slate-700/50 dark:bg-[#07101f]/95">
            <div class="flex px-6 py-5">
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
                    class="inline-flex cursor-pointer items-center gap-2 self-start rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-pink-500/30 hover:bg-slate-50 hover:text-slate-950 dark:border-slate-800/50 dark:bg-[#0b1324] dark:text-slate-300 dark:hover:bg-[#111a2d] dark:hover:text-white"
                >
                    <x-icons.chevron-left class="h-5 w-5" />
                    <span>Back</span>
                </a>

            </div>
        </section>

        <section class="border-x border-b border-slate-200 bg-white px-4 py-4 dark:border-slate-700/50 dark:bg-[#07101f]/95">
            @foreach($parentQuestions as $parentQuestion)
                <livewire:questions.show :questionId="$parentQuestion->id" :in-thread="true" :key="$parentQuestion->id" />
                <x-post-divider />
            @endforeach

            <livewire:questions.show :questionId="$question->id" :in-thread="false" :commenting="true" />

            <div class="mt-6 border border-slate-200/80 bg-slate-50/70 p-4 dark:border-slate-800/30 dark:bg-[#0b1324]">
                <x-comments :question="$question" />
            </div>
        </section>
    </div>
</x-app-layout>
