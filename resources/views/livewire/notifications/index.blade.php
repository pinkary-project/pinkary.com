<div class="mb-20 flex flex-col gap-2">
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
            <div class="group overflow-hidden rounded-2xl border border-slate-900 bg-slate-950 bg-opacity-80 p-4 transition-colors hover:bg-slate-900">
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
                            <figure class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-800 transition-opacity group-hover:opacity-90">
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
                            <figure class="{{ $question->to->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-800 transition-opacity group-hover:opacity-90">
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
                                <figure class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-800 transition-opacity group-hover:opacity-90">
                                    <img
                                        src="{{ $question->from->avatar_url }}"
                                        alt="{{ $question->from->username }}"
                                        class="{{ $question->from->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                                    />
                                </figure>
                                <p>{{ $question->from->name }} asked you:</p>
                            </div>
                        @endif
                    @endif
                @elseif ($question->parent !== null)
                    <p class="text-sm text-slate-500">You have been mentioned in a comment by {{ '@' . $question->to->username }}</p>
                @else
                    <p class="text-sm text-slate-500">You have been mentioned in a {{ $question->isSharedUpdate() ? 'update by @'.$question->to->username : 'question:'}}</p>
                @endif
                @if(!$question->isSharedUpdate())
                    <p class="mt-2 text-slate-200">
                        {!! $question->content !!}
                    </p>
                @endif
            </div>
        </a>
    @endforeach

    @if ($notifications->count() === 0)
        <div class="rounded-lg">
            <p class="text-slate-400">No pending notifications.</p>
        </div>
    @endif
</div>
