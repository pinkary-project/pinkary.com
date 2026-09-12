@props([
    'notification',
    'question',
    'user',
])

@php
    $isComment = $question->parent_id !== null;
    $isAnswer = ! $isComment && $question->from->is($user) && $question->answer !== null;
    $isAnonymous = ! $isComment && ! $isAnswer && $question->anonymously;

    if ($isComment) {
        $actor = $question->from;
        $action = 'commented on your '.($question->parent->parent_id !== null ? 'comment:' : ($question->parent->isSharedUpdate() ? 'Update:' : 'Answer:'));
    } elseif ($isAnswer) {
        $actor = $question->to;
        $action = 'answered your '.($question->anonymously ? 'anonymous question:' : 'question:');
    } elseif ($isAnonymous) {
        $actor = null;
        $action = 'asked you anonymously:';
    } else {
        $actor = $question->from;
        $action = 'asked you:';
    }

    $snippet = $question->content === '__UPDATE__' ? $question->answer : $question->content;
@endphp

<div class="flex items-start gap-3 sm:gap-4">
    @if ($actor !== null)
        <figure class="{{ $actor->is_company_verified ? 'rounded-2xl' : 'rounded-full' }} h-10 w-10 sm:h-12 sm:w-12 shrink-0 overflow-hidden border border-slate-200/70 bg-slate-100 transition-opacity group-hover:opacity-90 dark:border-slate-800/30 dark:bg-[#10182b]">
            <img
                src="{{ $actor->avatar_url }}"
                alt="{{ $actor->username }}"
                class="{{ $actor->is_company_verified ? 'rounded-2xl' : 'rounded-full' }} h-10 w-10 sm:h-12 sm:w-12"
            />
        </figure>
    @else
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-dashed border-slate-300 bg-slate-100 sm:h-12 sm:w-12 dark:border-slate-700 dark:bg-[#10182b]">
            <span class="text-sm font-medium text-slate-500 dark:text-slate-400">?</span>
        </div>
    @endif

    <div class="min-w-0 flex-1 py-0.5">
        <div class="flex items-center justify-between gap-x-2">
            <div class="flex min-w-0 flex-1 items-center gap-x-1.5 text-sm">
                <p class="truncate font-medium text-slate-950 dark:text-white">{{ $actor?->name ?? 'Anonymous' }}</p>

                @if ($actor?->is_verified && $actor?->is_company_verified)
                    <x-icons.verified-company :color="$actor->right_color" class="h-3.5 w-3.5 shrink-0" />
                @elseif ($actor?->is_verified)
                    <x-icons.verified :color="$actor->right_color" class="h-3.5 w-3.5 shrink-0" />
                @endif

                @if ($actor?->username)
                    <p class="truncate text-slate-500 transition-colors group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-300">
                        {{ '@'.$actor->username }}
                    </p>
                @endif
            </div>

            <div class="flex shrink-0 items-center gap-2 text-[0.82rem] text-slate-500 dark:text-slate-400">
                <time
                    class="inline-flex cursor-help items-center whitespace-nowrap"
                    title="{{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
                    datetime="{{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
                >
                    {{
                        $notification->created_at->timezone(session()->get('timezone', 'UTC'))
                            ->diffForHumans(short: true)
                    }}
                </time>
            </div>
        </div>

        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $action }}</p>

        @if (filled($snippet))
            <div class="mt-1.5 line-clamp-3 text-sm leading-6 break-words text-slate-700 dark:text-slate-200">
                {!! $snippet !!}
            </div>
        @endif
    </div>
</div>
