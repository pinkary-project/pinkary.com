@props([
    'notification',
    'url' => route('notifications.show', ['notification' => $notification->id]),
])

<a href="{{ $url }}" wire:navigate>
    <div class="group overflow-hidden rounded-md border border-slate-200/70 bg-white/90 p-4 shadow-sm shadow-slate-900/5 transition-colors hover:cursor-pointer hover:border-slate-300 hover:bg-slate-50 hover:shadow-md dark:border-slate-800/30 dark:bg-[#0b1324] dark:shadow-black/20 dark:hover:border-slate-700/40 dark:hover:bg-[#11192b]">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">{{ $slot }}</div>
            <div
                class="shrink-0 cursor-help text-right text-xs text-slate-500 dark:text-slate-400"
                title="{{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->isoFormat('ddd, D MMMM YYYY HH:mm') }}"
                datetime="{{ $notification->created_at->timezone(session()->get('timezone', 'UTC'))->toIso8601String() }}"
            >
                {{
                    $notification->created_at->timezone(session()->get('timezone', 'UTC'))
                        ->diffForHumans()
                }}
            </div>
        </div>
    </div>
</a>
