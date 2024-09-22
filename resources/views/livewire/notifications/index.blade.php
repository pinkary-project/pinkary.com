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
                    {{
                        $notification->created_at->timezone(session()->get('timezone', 'UTC'))
                            ->diffForHumans()
                    }}
                </div>
                @if (! $isMention)
                    @if ($question->parent_id !== null)
                        <div class="flex items center gap-3 text-sm text-slate-500">
                            <figure class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 dark:bg-slate-800 bg-slate-50 transition-opacity group-hover:opacity-90">
                                <img
                                    src="{{ $question->from->avatar_url }}"
                                    alt="{{ $question->from->username }}"
                                    class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                                />
                            </figure>
                            <p>{{ $question->from->name }} commented on your {{ $question->parent->parent_id !== null ? 'comment' : ($question->parent->isSharedUpdate() ? 'Update' : 'Answer') }}:
                        </div>
                    @elseif ($question->from->is(auth()->user()) && $question->answer !== null)
                        <div class="flex items-center gap-3 text-sm text-slate-500">
                            <figure class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 dark:bg-slate-800 bg-slate-50 transition-opacity group-hover:opacity-90">
                                <img
                                    src="{{ $question->to->avatar_url }}"
                                    alt="{{ $question->to->username }}"
                                    class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                                />
                            </figure>
                            <p>{{ $question->to->name }} answered your {{ $question->anonymously ? 'anonymous' : '' }} question:</p>
                        </div>
                    @else
                        @if ($question->anonymously)
                            <div class="flex items-center gap-3 text-sm text-slate-500">
                                <div class="border-1 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-dashed border-slate-400">
                                    <span>?</span>
                                </div>
                                <p>Someone asked you anonymously:</p>
                            </div>
                        @else
                            <div class="flex items-center gap-3 text-sm text-slate-500">
                                <figure class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 dark:bg-slate-800 bg-slate-50 transition-opacity group-hover:opacity-90">
                                    <img
                                        src="{{ $question->from->avatar_url }}"
                                        alt="{{ $question->from->username }}"
                                        class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                                    />
                                </figure>
                                <p><span class="dark:text-white text-black">{{ $question->from->name }}</span> asked you:</p>
                            </div>
                        @endif
                    @endif
                @elseif ($question->parent !== null)
                    <p class="text-sm text-slate-500">You have been mentioned in a comment by {{ '@' . $question->to->username }}</p>
                @else
                    <p class="text-sm text-slate-500">You have been mentioned in a {{ $question->isSharedUpdate() ? 'update by @'.$question->to->username : 'question:'}}</p>
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
