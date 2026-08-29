<div class="mt-4 space-y-2">
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
                        'group w-full rounded-lg text-left transition duration-200',
                        'cursor-not-allowed opacity-60' => $isDisabled,
                        'hover:opacity-90' => ! $isDisabled,
                    ])
                    @disabled($isDisabled)
                >
                    <div class="relative flex min-h-10 items-center overflow-hidden rounded-lg bg-slate-200/80 dark:bg-slate-800/90">
                        <div
                            @class([
                                'absolute inset-y-0 left-0 rounded-lg transition-all duration-500',
                                'bg-pink-500/90' => $isSelected,
                                'bg-slate-400 dark:bg-slate-600' => ! $isSelected,
                            ])
                            style="width: {{ $percentage }}%"
                        ></div>
                        <div class="relative flex w-full items-center justify-between gap-3 px-3.5 py-2.5">
                            <span @class([
                                'min-w-0 flex-1 break-words text-sm font-semibold',
                                'text-white' => $isSelected,
                                'text-slate-700 dark:text-slate-200' => ! $isSelected,
                            ])>
                                {{ $option->text }}
                            </span>
                            <div class="flex shrink-0 items-center gap-2">
                                @if ($isSelected)
                                    <x-heroicon-s-check-circle class="size-4 text-white" />
                                @endif
                                <span @class([
                                    'text-xs font-semibold tabular-nums',
                                    'text-white' => $isSelected,
                                    'text-slate-600 dark:text-slate-300' => ! $isSelected,
                                ])>
                                    {{ $percentage }}%
                                </span>
                            </div>
                        </div>
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
