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
            <li
                data-parent=true
                x-data="clickHandler"
                x-on:click="handleNavigation($event)"
                class="cursor-pointer transition hover:bg-slate-100 dark:hover:bg-slate-900/60"
            >
                <div class="flex items-center gap-3 px-6 py-4">
                    <img
                        src="{{ $user->avatar_url }}"
                        alt="{{ $user->username }}"
                        class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-9 w-9 shrink-0"
                    />

                    <div class="min-w-0 flex-1 flex flex-col">
                        <a
                            href="{{ route('profile.show', ['username' => $user->username]) }}"
                            class="truncate text-sm font-medium text-slate-950 dark:text-white"
                            wire:navigate
                            x-ref="parentLink"
                        >
                            {{ $user->name }}
                        </a>

                        <p class="truncate text-sm text-slate-400">
                            {{ '@'.$user->username }}
                        </p>
                    </div>

                    <x-follow-button
                        :id="$user->id"
                        :isFollower="auth()->check() && $user->is_follower"
                        :isFollowing="auth()->check() && $user->is_following"
                        class="ml-auto shrink-0"
                        wire:key="follow-button-{{ $user->id }}"
                    />
                </div>
            </li>
        @endforeach
    </ul>
</section>
