<x-app-layout>
    <div class="flex flex-col items-center py-10">
        <div class="flex w-full max-w-md flex-col gap-12 overflow-hidden"
             x-data
             x-init="document.getElementById('q-{{ $question->id }}').scrollIntoView();">
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
                class="flex text-slate-400 hover:underline cursor-pointer"
            >
                <x-icons.chevron-left class="h-6 w-6" />
                <span>Back</span>
            </a>

            @php
                $parentQuestion = $question->parent;

                do {
                    $parentQuestions[] = $parentQuestion;
                } while ($parentQuestion = $parentQuestion?->parent);
            @endphp

            @php $parentQuestions = collect($parentQuestions)->filter()->reverse(); @endphp

            @foreach($parentQuestions as $parentQuestion)
                <livewire:questions.show :questionId="$parentQuestion->id" :in-thread="false" />
                <div class="relative -mt-11 -mb-14 h-14">
                    <span class="absolute left-8 h-full w-1.5 rounded-full bg-slate-700" aria-hidden="true"></span>
                </div>
            @endforeach

            <livewire:questions.show :questionId="$question->id" :in-thread="true" :commenting="true" />
        </div>
    </div>
</x-app-layout>
