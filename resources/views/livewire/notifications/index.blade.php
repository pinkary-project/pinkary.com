<div class="mb-20 flex flex-col gap-3">
    @if ($notifications->isNotEmpty())
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
            <div class="group overflow-hidden rounded-md border border-slate-200/70 bg-white/90 p-4 shadow-sm shadow-slate-900/5 transition-colors hover:cursor-pointer hover:border-slate-300 hover:bg-slate-50 hover:shadow-md dark:border-slate-800/30 dark:bg-[#0b1324] dark:shadow-black/20 dark:hover:border-slate-700/40 dark:hover:bg-[#11192b]">
                <div
                    class="cursor-help text-right text-xs text-slate-500 dark:text-slate-400"
                    title="{{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
                    datetime="{{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
                >
                    {{
                        $notification->created_at->timezone(session()->get('timezone', 'UTC'))
                            ->diffForHumans()
                    }}
                </div>
                @if (! $isMention)
                    @if ($question->parent_id !== null)
                        <div class="mt-3 flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                            <figure class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-100 transition-opacity group-hover:opacity-90 dark:bg-slate-800">
                                <img
                                    src="{{ $question->from->avatar_url }}"
                                    alt="{{ $question->from->username }}"
                                    class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                                />
                            </figure>
                            <p><span class="font-medium text-slate-950 dark:text-white">{{ $question->from->name }}</span> commented on your {{ $question->parent->parent_id !== null ? 'comment' : ($question->parent->isSharedUpdate() ? 'Update' : 'Answer') }}:</p>
                        </div>
                    @elseif ($question->from->is(auth()->user()) && $question->answer !== null)
                        <div class="mt-3 flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                            <figure class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-100 transition-opacity group-hover:opacity-90 dark:bg-slate-800">
                                <img
                                    src="{{ $question->to->avatar_url }}"
                                    alt="{{ $question->to->username }}"
                                    class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                                />
                            </figure>
                            <p><span class="font-medium text-slate-950 dark:text-white">{{ $question->to->name }}</span> answered your {{ $question->anonymously ? 'anonymous question' : 'question' }}:</p>
                        </div>
                    @else
                        @if ($question->anonymously)
                            <div class="mt-3 flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                <div class="border-1 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-dashed border-slate-400">
                                    <span>?</span>
                                </div>
                                <p>Someone asked you anonymously:</p>
                            </div>
                        @else
                            <div class="mt-3 flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                <figure class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-100 transition-opacity group-hover:opacity-90 dark:bg-slate-800">
                                    <img
                                        src="{{ $question->from->avatar_url }}"
                                        alt="{{ $question->from->username }}"
                                        class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                                    />
                                </figure>
                                <p><span class="font-medium text-slate-950 dark:text-white">{{ $question->from->name }}</span> asked you:</p>
                            </div>
                        @endif
                    @endif
                @elseif ($question->parent !== null)
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">You have been mentioned in a comment by <span class="font-medium text-slate-950 dark:text-white">{{ '@' . $question->to->username }}</span></p>
                @else
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">You have been mentioned in a {{ $question->isSharedUpdate() ? 'update by ' : 'question by ' }}<span class="font-medium text-slate-950 dark:text-white">{{ '@' . $question->to->username }}</span></p>
                @endif
                @if(!$question->isSharedUpdate())
                    <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-200">
                        {!! $question->content !!}
                    </p>
                @endif
            </div>
        </a>
    @endforeach

    @if ($notifications->isEmpty())
        <div class="flex min-h-[24rem] items-center justify-center rounded-md border border-dashed border-slate-300/80 bg-slate-50/70 px-6 text-center dark:border-slate-700/80 dark:bg-slate-900/50">
            <div>
                <p class="text-lg font-medium text-slate-950 dark:text-white">No pending notifications.</p>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">New replies, mentions, and questions will show up here.</p>
            </div>
        </div>
    @endif
</div>
