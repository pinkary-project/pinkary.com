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
                data-parent="true"
                x-data="clickHandler"
                x-on:click="handleNavigation($event)"
                class="cursor-pointer transition hover:bg-slate-100 dark:hover:bg-slate-900/60"
            >
                <div class="px-5 py-3.5 sm:px-6 sm:py-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-3">
                            <img
                                src="{{ $user->avatar_url }}"
                                alt="{{ $user->username }}"
                                class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-9 w-9 shrink-0"
                            />

                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <a
                                        href="{{ route('profile.show', ['username' => $user->username]) }}"
                                        class="truncate text-sm font-medium text-slate-950 hover:underline dark:text-white"
                                        wire:navigate
                                        x-ref="parentLink"
                                    >
                                        {{ $user->name }}
                                    </a>

                                    @if ($user->is_verified && $user->is_company_verified)
                                        <x-icons.verified-company
                                            :color="$user->right_color"
                                            class="size-3.5 shrink-0"
                                        />
                                    @elseif ($user->is_verified)
                                        <x-icons.verified :color="$user->right_color" class="size-3.5 shrink-0" />
                                    @endif
                                </div>

                                <p class="truncate text-xs text-slate-400 dark:text-slate-500">
                                    {{ '@'.$user->username }}
                                </p>
                            </div>
                        </div>

                        <x-follow-button
                            :id="$user->id"
                            :isFollower="auth()->check() && $user->is_follower"
                            :isFollowing="auth()->check() && $user->is_following"
                            class="shrink-0"
                            wire:key="follow-button-{{ $user->id }}"
                        />
                    </div>

                    @if ($user->bio)
                        <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                            {{ $user->bio }}
                        </p>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
</section>
