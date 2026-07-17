<div class="w-full text-slate-700 dark:text-slate-200">
    <div class="border-r border-b border-slate-200/70 bg-white/80 p-6 dark:border-slate-800/30 dark:bg-[#071121]/95">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-slate-950 dark:text-white">People</h3>
            </div>

            <p class="max-w-sm text-sm leading-6 text-slate-500 dark:text-slate-400">
                Discover new profiles to follow.
            </p>
        </div>

        <div class="relative mt-4 flex items-center">
            <x-heroicon-o-magnifying-glass class="absolute left-4 z-10 size-5 text-slate-400 dark:text-slate-500" />

            <x-text-input
                x-ref="searchInput"
                x-init="if ($wire.focusInput) $refs.searchInput.focus();"
                wire:model.live.debounce.500ms="query"
                name="q"
                placeholder="Search for users..."
                class="w-full rounded-none! border-slate-200/70! bg-white! py-3! pl-12! text-slate-950! shadow-none placeholder:text-slate-400! dark:border-slate-800/30! dark:bg-[#050d1b]! dark:text-white! dark:placeholder:text-slate-500!"
            />
        </div>
    </div>

    @if ($users->isEmpty())
        <section class="border-r border-b border-dashed border-slate-300/80 bg-slate-50/70 p-6 text-center dark:border-slate-800/50 dark:bg-[#071121]/95">
            <p class="text-lg font-medium text-slate-950 dark:text-white">No users found.</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Try another search or clear the query to return to the discovery list.
            </p>
        </section>
    @else
        <section>
            <ul class="divide-y divide-slate-200/70 border-r border-slate-200/70 bg-white/70 dark:divide-slate-800/30 dark:border-slate-800/30 dark:bg-[#071121]/60">
                @foreach ($users as $user)
                    <li
                        data-parent="true"
                        x-data="clickHandler"
                        x-on:click="handleNavigation($event)"
                        wire:key="user-{{ $user->id }}"
                    >
                        <div class="group flex items-center gap-3 px-6 py-4 transition-colors hover:bg-slate-50 dark:hover:bg-[#0a1325]">
                            <figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 shrink-0 overflow-hidden bg-slate-100 transition-opacity group-hover:opacity-90 dark:bg-slate-800">
                                <img
                                    class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
                                    src="{{ $user->avatar_url }}"
                                    alt="{{ $user->username }}"
                                />
                            </figure>
                            <div class="flex min-w-0 flex-1 flex-col overflow-hidden text-left text-sm">
                                <a
                                    class="flex items-center gap-2"
                                    href="{{ route('profile.show', ['username' => $user->username]) }}"
                                    wire:navigate
                                    x-ref="parentLink"
                                >
                                    <p class="truncate font-medium text-slate-950 dark:text-white">{{ $user->name }}</p>

                                    @if ($user->is_verified && $user->is_company_verified)
                                        <x-icons.verified-company :color="$user->right_color" class="size-4" />
                                    @elseif ($user->is_verified)
                                        <x-icons.verified :color="$user->right_color" class="size-4" />
                                    @endif
                                </a>
                                <p class="truncate text-slate-500 transition-colors group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-300">
                                    {{ '@'.$user->username }}
                                </p>
                            </div>
                            <x-follow-button
                                :id="$user->id"
                                :isFollower="auth()->check() && $user->is_follower"
                                :isFollowing="auth()->check() && $user->is_following"
                                class="ml-auto"
                                wire:key="follow-button-{{ $user->id }}"
                            />
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
