<div class="mt-4 space-y-3">
    @error('poll')
        <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
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
                        'w-full text-left p-3 rounded-lg border transition-colors duration-200',
                        'cursor-not-allowed opacity-60' => $isDisabled,
                        'hover:bg-slate-50 dark:hover:bg-slate-800' => !$isDisabled,
                        'border-pink-500 bg-pink-50 dark:bg-pink-900/20' => $isSelected,
                        'border-slate-200 dark:border-slate-700' => !$isSelected,
                    ])
                    @disabled($isDisabled)
                >
                    <div class="flex items-center justify-between">
                        <span @class([
                            'font-medium',
                            'text-pink-700 dark:text-pink-300' => $isSelected,
                            'dark:text-slate-200 text-slate-800' => !$isSelected,
                        ])>
                            {{ $option->text }}
                        </span>
                        <div class="flex items-center gap-2">
                            @if ($totalVotes > 0)
                                <span @class([
                                    'text-sm',
                                    'text-pink-600 dark:text-pink-400' => $isSelected,
                                    'text-slate-500 dark:text-slate-400' => !$isSelected,
                                ])>
                                    {{ $percentage }}%
                                </span>
                            @endif
                            @if ($isSelected)
                                <x-heroicon-s-check-circle class="h-4 w-4 text-pink-500" />
                            @endif
                        </div>
                    </div>

                    @if ($totalVotes > 0)
                        <div class="mt-2 w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                            <div
                                @class([
                                    'h-2 rounded-full transition-all duration-500',
                                    'bg-pink-500' => $isSelected,
                                    'bg-slate-400 dark:bg-slate-500' => !$isSelected,
                                ])
                                style="width: {{ $percentage }}%"
                            ></div>
                        </div>
                    @endif
                </button>
            </div>
        @endforeach

        <div class="pt-2 text-sm text-slate-500 dark:text-slate-400">
            {{ $totalVotes }} {{ $totalVotes === 1 ? 'vote' : 'votes' }}
            @if ($isPollExpired)
                · <span class="text-red-500">Poll expired</span>
            @elseif ($timeRemaining)
                · <span class="text-slate-600 dark:text-slate-300">Ends {{ $timeRemaining }}</span>
            @endif
            @if (!$isPollExpired && auth()->guest())
                · <a href="{{ route('login') }}" data-navigate-ignore="true" class="text-pink-500 hover:text-pink-600">Sign in to vote</a>
            @endif
        </div>
    @endif
</div>
