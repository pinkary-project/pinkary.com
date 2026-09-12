<div class="mb-20 flex flex-col gap-3">
    @if ($notifications->count() > 0)
        <div class="mb-2 flex items-center justify-end">
            <button
                class="inline-flex items-center rounded-full border border-slate-200/70 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800/30 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                wire:click="ignoreAll('{{ now() }}')"
            >
                Ignore all
            </button>
        </div>
    @endif

    @foreach ($notifications as $notification)
        @if ($notification->type === 'App\Notifications\UserMentioned')
            @php
                /** @var Question|null $question */
                $question = $questions->get($notification->data['question_id'] ?? null);
            @endphp

            @if ($question !== null)
                <x-notifications.item :notification="$notification">
                    <x-notifications.mention :notification="$notification" :question="$question" />
                </x-notifications.item>
            @endif
        @elseif (in_array($notification->type, ['App\Notifications\QuestionCreated', 'App\Notifications\QuestionAnswered'], true))
            @php
                /** @var Question|null $question */
                $question = $questions->get($notification->data['question_id'] ?? null);
            @endphp

            @if ($question !== null)
                <x-notifications.item :notification="$notification">
                    <x-notifications.question :notification="$notification" :question="$question" :user="$user" />
                </x-notifications.item>
            @endif
        @endif
    @endforeach

    @if ($notifications->hasPages())
        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif

    @if ($notifications->count() === 0)
        <div class="flex min-h-96 items-center justify-center rounded-md border border-dashed border-slate-300/80 bg-slate-50/70 px-6 text-center dark:border-slate-700/80 dark:bg-slate-900/50">
            <div>
                <p class="text-lg font-medium text-slate-950 dark:text-white">No pending notifications.</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    New replies, mentions, and questions will show up here.
                </p>
            </div>
        </div>
    @endif
</div>
