@props([
    'notification',
    'question',
    'repost',
])

<div class="flex items-start gap-3 sm:gap-4">
    <figure class="{{ $repost->user->is_company_verified ? 'rounded-2xl' : 'rounded-full' }} h-10 w-10 sm:h-12 sm:w-12 shrink-0 overflow-hidden border border-slate-200/70 bg-slate-100 dark:border-slate-800/30 dark:bg-[#10182b]">
        <img
            src="{{ $repost->user->avatar_url }}"
            alt="{{ $repost->user->username }}"
            class="{{ $repost->user->is_company_verified ? 'rounded-2xl' : 'rounded-full' }} h-10 w-10 sm:h-12 sm:w-12"
        />
    </figure>

    <div class="min-w-0 flex-1 py-0.5">
        <div class="flex items-center justify-between gap-x-2">
            <div class="flex min-w-0 flex-1 items-center gap-x-1.5 text-sm">
                <p class="truncate font-medium text-slate-950 dark:text-white">{{ $repost->user->name }}</p>

                @if ($repost->user->is_verified && $repost->user->is_company_verified)
                    <x-icons.verified-company :color="$repost->user->right_color" class="h-3.5 w-3.5 shrink-0" />
                @elseif ($repost->user->is_verified)
                    <x-icons.verified :color="$repost->user->right_color" class="h-3.5 w-3.5 shrink-0" />
                @endif

                <p class="truncate text-slate-500 dark:text-slate-400">{{ '@'.$repost->user->username }}</p>
            </div>

            <time
                class="inline-flex shrink-0 cursor-help items-center text-[0.82rem] whitespace-nowrap text-slate-500 dark:text-slate-400"
                title="{{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
                datetime="{{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
            >
                {{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->diffForHumans(short: true) }}
            </time>
        </div>

        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">reposted your post:</p>

        @if (filled($question->content))
            <div class="mt-1.5 line-clamp-3 text-sm leading-6 break-words text-slate-700 dark:text-slate-200">
                {!! $question->content !!}
            </div>
        @endif
    </div>
</div>
