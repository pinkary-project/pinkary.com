<x-app-layout>
    <div class="flex flex-col items-center">
        <div class="flex w-full flex-col gap-1"
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
                class="flex dark:text-slate-400 pl-6 py-3 xl:pl-8 xl:py-4 text-slate-500 hover:underline cursor-pointer"
            >
                <x-icons.chevron-left class="h-6 w-6" />
                <span>Back</span>
            </a>

            <ul role="list" class="divide-y divide-white/5">
            @foreach($parentQuestions as $parentQuestion)
                <li class="cursor-pointer hover:bg-gray-800/20">
                    <livewire:questions.show :questionId="$parentQuestion->id" :in-thread="true" :key="$parentQuestion->id" />
                </li>
                <x-post-divider />
            @endforeach
                <li class="cursor-pointer hover:bg-gray-800/20">
                    <livewire:questions.show :questionId="$question->id" :in-thread="true" :commenting="true" />
                </li>

                <x-comments :question="$question" />
            </ul>
        </div>
    </div>
</x-app-layout>
