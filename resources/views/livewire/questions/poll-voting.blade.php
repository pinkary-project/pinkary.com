<div class="mt-4 space-y-2.5">
    @error('poll')
        <div class="rounded-xl border border-red-200/70 bg-red-50/80 p-3 dark:border-red-900/60 dark:bg-red-950/20">
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        </div>
    @enderror

    @if ($pollOptions->count() > 0)
        @foreach ($pollOptions as $option)
            @php
                $percentage = $totalVotes > 0 ? round(($option->votes_count / $totalVotes) * 100) : 0;
                $isSelected = $userVote?->poll_option_id === $option->id;
                $isDisabled = $isPollExpired || auth()->guest();
            @endphp

            <div class="relative">
                <button
                    wire:click="vote({{ $option->id }})"
                    data-navigate-ignore="true"
                    @class([
                        'group w-full rounded-xl border px-3.5 py-3 text-left transition duration-200',
                        'cursor-not-allowed opacity-60' => $isDisabled,
                        'hover:border-pink-300 hover:bg-pink-50/50 dark:hover:border-pink-700 dark:hover:bg-pink-950/20' => ! $isDisabled,
                        'border-pink-500 bg-pink-50/70 dark:border-pink-500 dark:bg-pink-950/25' => $isSelected,
                        'border-slate-200/80 bg-white dark:border-slate-800/80 dark:bg-[#0b1324]' => ! $isSelected,
                    ])
                    @disabled($isDisabled)
                >
                    <div class="flex items-center justify-between gap-3">
                        <span @class([
                            'min-w-0 flex-1 break-words text-sm font-semibold',
                            'text-pink-700 dark:text-pink-300' => $isSelected,
                            'dark:text-slate-200 text-slate-800' => ! $isSelected,
                        ])>
                            {{ $option->text }}
                        </span>
                        <div class="flex shrink-0 items-center gap-2">
                            <span @class([
                                'text-xs font-medium tabular-nums',
                                'text-pink-600 dark:text-pink-400' => $isSelected,
                                'text-slate-500 dark:text-slate-400' => ! $isSelected,
                            ])>
                                {{ $percentage }}%
                            </span>
                            @if ($isSelected)
                                <x-heroicon-s-check-circle class="h-4 w-4 text-pink-500" />
                            @endif
                        </div>
                    </div>

                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700/90">
                        <div
                            @class([
                                'h-full rounded-full transition-all duration-500',
                                'bg-pink-500' => $isSelected,
                                'bg-slate-400 dark:bg-slate-500' => ! $isSelected,
                            ])
                            style="width: {{ $percentage }}%"
                        ></div>
                    </div>
                </button>
            </div>
        @endforeach

        <div class="pt-2 text-sm text-slate-500 dark:text-slate-400">
            {{ $totalVotes }} {{ $totalVotes === 1 ? 'vote' : 'votes' }}
            @if ($isPollExpired)
                ·
                <span class="text-red-500">Poll expired</span>
            @elseif ($timeRemaining)
                ·
                <span class="text-slate-600 dark:text-slate-300">Ends {{ $timeRemaining }}</span>
            @endif
            @if (! $isPollExpired && auth()->guest())
                ·
                <a href="{{ route('login') }}" data-navigate-ignore="true" class="text-pink-500 hover:text-pink-600"
                    >Sign in to vote</a>
            @endif
        </div>
    @endif
</div>
