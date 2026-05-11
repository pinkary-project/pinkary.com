<div class="mb-12 w-full text-slate-400 dark:text-slate-200">
    <div class="mb-8 rounded-[1.75rem] border border-slate-200/70 bg-slate-50/80 p-3 dark:border-slate-800/70 dark:bg-slate-900/70 sm:p-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">People</p>
                <h3 class="mt-2 font-mona text-xl font-semibold text-slate-950 dark:text-white">Find the next profile to follow.</h3>
            </div>

            <p class="max-w-sm text-sm leading-6 text-slate-500 dark:text-slate-400">
                Search by name or username, or browse the current discovery list powered by existing user data.
            </p>
        </div>

        <div class="relative mt-4 flex items-center">
            <x-heroicon-o-magnifying-glass class="absolute left-5 z-10 size-5 text-slate-400"/>

            <x-text-input
                x-ref="searchInput"
                x-init="if ($wire.focusInput) $refs.searchInput.focus()"
                wire:model.live.debounce.500ms="query"
                name="q"
                placeholder="Search for users..."
                class="w-full !rounded-[1.25rem] !border-slate-200/80 !bg-white/90 py-3 pl-14 dark:!border-slate-800 dark:!bg-slate-950/80"
            />
        </div>
    </div>

    @if ($users->isEmpty())
        <section class="rounded-[1.75rem] border border-dashed border-slate-300/80 bg-slate-50/70 p-8 text-center dark:border-slate-700/80 dark:bg-slate-900/50">
            <p class="text-lg font-medium text-slate-950 dark:text-white">No users found.</p>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Try another search or clear the query to return to the discovery list.</p>
        </section>
    @else
        <section>
            <ul class="flex flex-col gap-3">
                @foreach ($users as $user)
                    <li
                        data-parent=true
                        x-data="clickHandler"
                        x-on:click="handleNavigation($event)"
                        wire:key="user-{{ $user->id }}"
                    >
                        <div class="group flex items-center gap-3 rounded-[1.75rem] border border-slate-200/70 bg-slate-50/80 p-4 transition-colors hover:bg-white dark:border-slate-800/70 dark:bg-slate-900/70 dark:hover:bg-slate-900/90 sm:p-5">
                            <figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12 flex-shrink-0 overflow-hidden bg-slate-800 transition-opacity group-hover:opacity-90">
                                <img
                                    class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12"
                                    src="{{ $user->avatar_url }}"
                                    alt="{{ $user->username }}"
                                />
                            </figure>
                            <div class="min-w-0 flex flex-1 flex-col overflow-hidden text-left text-sm">
                                <a
                                    class="flex items-center gap-2"
                                    href="{{ route('profile.show', ['username' => $user->username]) }}"
                                    wire:navigate
                                    x-ref="parentLink"
                                >
                                    <p class="text-wrap truncate font-medium dark:text-white text-black">
                                        {{ $user->name }}
                                    </p>

                                    @if ($user->is_verified && $user->is_company_verified)
                                        <x-icons.verified-company
                                            :color="$user->right_color"
                                            class="size-4"
                                        />
                                    @elseif ($user->is_verified)
                                        <x-icons.verified
                                            :color="$user->right_color"
                                            class="size-4"
                                        />
                                    @endif
                                </a>
                                <p class="truncate text-slate-500 transition-colors group-hover:text-slate-600 dark:group-hover:text-slate-300">
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
