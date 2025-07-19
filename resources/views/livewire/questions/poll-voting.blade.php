<div class="mt-4 space-y-3">
    @if ($pollOptions->count() > 0)
        @foreach ($pollOptions as $option)
            @php
                $percentage = $totalVotes > 0 ? round(($option->votes_count / $totalVotes) * 100) : 0;
                $isSelected = $userVote && $userVote->poll_option_id === $option->id;
            @endphp

            <div class="relative">
                <button
                    wire:click="vote({{ $option->id }})"
                    class="w-full text-left p-3 rounded-lg border transition-colors duration-200 hover:bg-slate-50 dark:hover:bg-slate-800 {{ $isSelected ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : 'border-slate-200 dark:border-slate-700' }}"
                    @guest disabled @endguest
                >
                    <div class="flex items-center justify-between">
                        <span class="font-medium {{ $isSelected ? 'text-pink-700 dark:text-pink-300' : 'dark:text-slate-200 text-slate-800' }}">
                            {{ $option->text }}
                        </span>
                        <div class="flex items-center gap-2">
                            @if ($totalVotes > 0)
                                <span class="text-sm {{ $isSelected ? 'text-pink-600 dark:text-pink-400' : 'text-slate-500 dark:text-slate-400' }}">
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
                                class="h-2 rounded-full transition-all duration-500 {{ $isSelected ? 'bg-pink-500' : 'bg-slate-400 dark:bg-slate-500' }}"
                                style="width: {{ $percentage }}%"
                            ></div>
                        </div>
                    @endif
                </button>
            </div>
        @endforeach

        <div class="pt-2 text-sm text-slate-500 dark:text-slate-400">
            {{ $totalVotes }} {{ $totalVotes === 1 ? 'vote' : 'votes' }}
            @guest
                · <a href="{{ route('login') }}" class="text-pink-500 hover:text-pink-600">Sign in to vote</a>
            @endguest
        </div>
    @endif
</div>
