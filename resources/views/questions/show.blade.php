<x-app-layout>
    <div class="flex flex-col items-center py-10">
        <div class="flex w-full max-w-md flex-col gap-12"
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
                class="flex dark:text-slate-400 text-slate-500 hover:underline cursor-pointer"
            >
                <x-icons.chevron-left class="h-6 w-6" />
                <span>Back</span>
            </a>

            <div>
                @foreach($parentQuestions as $parentQuestion)
                    <livewire:questions.show :questionId="$parentQuestion->id" :in-thread="true" :key="$parentQuestion->id" />
                    <x-post-divider />
                @endforeach

                <livewire:questions.show :questionId="$question->id" :in-thread="true" :commenting="true" />

                <x-comments :question="$question" />
            </div>
        </div>
    </div>
</x-app-layout>
