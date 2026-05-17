<section class="overflow-hidden border border-slate-200 bg-white/80 dark:border-slate-700/50 dark:bg-[#071121]/95">
    <div class="flex items-center justify-between border-b border-slate-200/70 px-4 py-4 dark:border-slate-800/30">
        <h2 class="text-[1.05rem] font-semibold text-slate-950 dark:text-white">People to follow</h2>

        <a
            href="{{ route('home.users') }}"
            class="text-sm font-medium text-pink-500 transition hover:text-pink-400"
            wire:navigate
        >
            View all
        </a>
    </div>

    <ul class="divide-y divide-slate-200/70 dark:divide-slate-800/30">
        @foreach ($users as $user)
            <li>
                <a
                    href="{{ route('profile.show', ['username' => $user->username]) }}"
                    class="flex items-center gap-3 px-6 py-4 transition hover:bg-slate-100 dark:hover:bg-slate-900/60"
                    wire:navigate
                >
                    <img
                        src="{{ $user->avatar_url }}"
                        alt="{{ $user->username }}"
                        class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-9 w-9 flex-shrink-0"
                    />

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-950 dark:text-white">
                            {{ $user->name }}
                        </p>

                        <p class="truncate text-sm text-slate-400">
                            {{ '@'.$user->username }}
                        </p>
                    </div>

                    <span class="flex-shrink-0 text-xs text-slate-500">
                        {{ $user->created_at->diffForHumans(short: true) }}
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</section>
