@props([
    'notification',
    'follower',
])

<div class="flex items-start gap-3 sm:gap-4">
    <figure class="{{ $follower->is_company_verified ? 'rounded-2xl' : 'rounded-full' }} h-10 w-10 sm:h-12 sm:w-12 shrink-0 overflow-hidden border border-slate-200/70 bg-slate-100 transition-opacity group-hover:opacity-90 dark:border-slate-800/30 dark:bg-[#10182b]">
        <img
            src="{{ $follower->avatar_url }}"
            alt="{{ $follower->username }}"
            class="{{ $follower->is_company_verified ? 'rounded-2xl' : 'rounded-full' }} h-10 w-10 sm:h-12 sm:w-12"
        />
    </figure>

    <div class="min-w-0 flex-1 py-0.5">
        <div class="flex items-center justify-between gap-x-2">
            <div class="flex min-w-0 flex-1 items-center gap-x-1.5 text-sm">
                <p class="truncate font-medium text-slate-950 dark:text-white">{{ $follower->name }}</p>

                @if ($follower->is_verified && $follower->is_company_verified)
                    <x-icons.verified-company :color="$follower->right_color" class="h-3.5 w-3.5 shrink-0" />
                @elseif ($follower->is_verified)
                    <x-icons.verified :color="$follower->right_color" class="h-3.5 w-3.5 shrink-0" />
                @endif

                <p class="truncate text-slate-500 transition-colors group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-300">
                    {{ '@'.$follower->username }}
                </p>
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

        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">followed you</p>
    </div>
</div>
