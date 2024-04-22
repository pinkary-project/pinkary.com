<article class="block">
    <div>
        <div class="flex justify-between">
            @if ($question->anonymously)
                <x-questions.anonymously />
            @else
                <a
                    href="{{ route('profile.show', ['username' => $question->from->username]) }}"
                    class="group flex items-center gap-3 px-4"
                    wire:navigate
                >
                    <x-questions.avatar :user="$question->from" />

                    <div class="overflow-hidden text-sm">
                        <div class="flex">
                            <p class="truncate font-medium text-slate-50">
                                {{ $question->from->name }}
                            </p>

                            @if ($question->from->is_verified && $question->from->is_company_verified)
                                <x-icons.verified-company :color="$question->from->right_color" class="ml-1 mt-0.5 h-3.5 w-3.5" />
                            @elseif ($question->from->is_verified)
                                <x-icons.verified :color="$question->from->right_color" class="ml-1 mt-0.5 h-3.5 w-3.5" />
                            @endif
                        </div>

                        <p class="truncate text-slate-500 transition-colors group-hover:text-slate-400">
                            {{ '@'.$question->from->username }}
                        </p>
                    </div>
                </a>
            @endif
            <x-questions.pinned
                :question="$question"
                :pinnable="$pinnable"
            />
        </div>

        <p class="mb-4 mt-3 px-4 text-slate-200">
            {!! $question->content !!}
        </p>
    </div>

    @if ($question->answer)
        <div class="answer mt-3 rounded-2xl bg-slate-900 p-4">
            <div class="flex justify-between">
                <a href="{{ route('profile.show', ['username' => $question->to->username]) }}" class="group flex items-center gap-3" wire:navigate>

                    <x-questions.avatar :user="$question->to" />

                    <div class="overflow-hidden text-sm">
                        <div class="items flex">
                            <p class="truncate font-medium text-slate-50">
                                {{ $question->to->name }}
                            </p>

                            @if ($question->to->is_verified && $question->to->is_company_verified)
                                <x-icons.verified-company :color="$question->to->right_color" class="ml-1 mt-0.5 h-3.5 w-3.5" />
                            @elseif ($question->to->is_verified)
                                <x-icons.verified :color="$question->to->right_color" class="ml-1 mt-0.5 h-3.5 w-3.5" />
                            @endif
                        </div>

                        <p class="truncate text-slate-500 transition-colors group-hover:text-slate-400">
                            {{ '@'.$question->to->username }}
                        </p>
                    </div>
                </a>
                <x-questions.actions :question="$question" />
            </div>

            <p class="mt-3 break-words text-slate-200">
                {!! $question->answer !!}
            </p>

            <div class="mt-3 flex items-center justify-between text-sm text-slate-500">
                <div class="flex items-center">
                    <x-questions.like :question="$question" />
                    <x-questions.views :question="$question" />
                </div>
                <div class="flex items-center text-slate-500">
                    <x-questions.answered-at :question="$question" />
                    <span class="mx-1">•</span>
                    <x-questions.share :question="$question" />
                    <x-questions.copy-link :question="$question" />
                </div>
            </div>
        </div>
    @elseif (auth()->user()?->is($user))
        <livewire:questions.edit :questionId="$question->id" :key="$question->id" />
    @endif
</article>
