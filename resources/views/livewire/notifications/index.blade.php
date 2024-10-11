<div class="mb-20 flex flex-col gap-2">
    @if ($notifications->isNotEmpty())
        <div class="flex items-center justify-end mb-2">
            <button
                class="dark:text-slate-400 text-slate-600"
                wire:click="ignoreAll('{{ now() }}')"
            >
                Ignore all
            </button>
        </div>
    @endif

    @foreach ($notifications as $notification)
        @php
            $question = \App\Models\Question::find($notification->data['question_id']);
            $isMention = $notification->type === 'App\Notifications\UserMentioned';

            if ($question === null) {
                $notification->delete();

                continue;
            }
        @endphp

        <a
            href="{{ route('notifications.show', ['notification' => $notification->id]) }}"
            wire:navigate
        >
            <div class="group overflow-hidden rounded-2xl border dark:border-slate-900 border-slate-200 dark:bg-slate-950 bg-slate-50 bg-opacity-80 p-4 transition-colors dark:hover:bg-slate-900 hover:bg-slate-100 hover:cursor-pointer">
                <div
                    class="cursor-help text-right text-xs text-slate-400"
                    title="{{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
                    datetime="{{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
                >
                    {{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->diffForHumans() }}
                </div>

                @if (! $isMention)
                    @if ($question->parent_id !== null)
                        @include('livewire.notifications.partials.commented', ['question' => $question])
                    @elseif ($question->from->is(auth()->user()) && $question->answer !== null)
                        @include('livewire.notifications.partials.answered', ['question' => $question])
                    @else
                        @if ($question->anonymously)
                            @include('livewire.notifications.partials.question-anon')
                        @else
                            @include('livewire.notifications.partials.question', ['question' => $question])
                        @endif
                    @endif
                @elseif ($question->parent !== null)
                    @include('livewire.notifications.partials.mentioned-comment', ['question' => $question])
                @else
                    @include('livewire.notifications.partials.mentioned-question', ['question' => $question])
                @endif

                @if(!$question->isSharedUpdate())
                    <p class="mt-2 dark:text-slate-200 text-slate-800">
                        {!! $question->content !!}
                    </p>
                @endif
            </div>
        </a>
    @endforeach

    @if ($notifications->isEmpty())
        <div class="rounded-lg">
            <p class="text-slate-400">No pending notifications.</p>
        </div>
    @endif
</div>
