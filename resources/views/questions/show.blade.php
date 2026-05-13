<x-app-layout>
    <div class="py-0"
         x-data
         x-init="document.getElementById('q-{{ $question->id }}').scrollIntoView();">
        <section class="overflow-hidden border-b border-r border-slate-800/30 bg-[#07101f]/95">
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
                    class="inline-flex cursor-pointer items-center gap-2 self-start rounded-md border border-slate-800/50 bg-[#0b1324] px-4 py-2 text-sm font-semibold text-slate-300 transition hover:border-pink-500/30 hover:bg-[#111a2d] hover:text-white"
                >
                    <x-icons.chevron-left class="h-5 w-5" />
                    <span>Back</span>
                </a>

            </div>
        </section>

        <section class="border-b border-r border-slate-800/30 bg-[#07101f]/95 px-6 py-6">
            @foreach($parentQuestions as $parentQuestion)
                <livewire:questions.show :questionId="$parentQuestion->id" :in-thread="true" :key="$parentQuestion->id" />
                <x-post-divider />
            @endforeach

            <livewire:questions.show :questionId="$question->id" :in-thread="true" :commenting="true" />

            <div class="mt-6 border border-slate-800/30 bg-[#0b1324] p-4">
                <x-comments :question="$question" />
            </div>
        </section>
    </div>
</x-app-layout>
