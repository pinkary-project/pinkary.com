<div>
    <div class="sticky -top-1 z-30 flex items-center justify-between border-b border-slate-200/70 bg-white px-6 py-4 sm:bg-white/90 sm:py-6 sm:backdrop-blur dark:border-slate-800/30 dark:bg-[#07101f] dark:sm:bg-[#07101f]/95">
        <h2 class="text-[2rem] font-semibold tracking-tight text-slate-950 dark:text-white">Notifications</h2>

        @if ($notifications->count() > 0)
            <button
                class="inline-flex items-center rounded-full border border-slate-200/70 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800/30 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                wire:click="ignoreAll('{{ now() }}')"
            >
                Ignore all
            </button>
        @endif
    </div>

    <div class="min-h-screen p-4 sm:p-6">
        <div class="mb-20 flex flex-col gap-3">
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
                            <x-notifications.question
                                :notification="$notification"
                                :question="$question"
                                :user="$user"
                            />
                        </x-notifications.item>
                    @endif
                @elseif ($notification->type === 'App\Notifications\UserFollowed')
                    @php
                        /** @var User|null $follower */
                        $follower = $followers->get($notification->data['follower_id'] ?? null);
                    @endphp

                    @if ($follower !== null)
                        <x-notifications.item :notification="$notification">
                            <x-notifications.user-followed :notification="$notification" :follower="$follower" />
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
                            New replies, mentions, followers, and questions will show up here.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
