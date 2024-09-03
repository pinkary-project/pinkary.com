<div class="w-full mb-12 text-slate-200">
    <div class="w-full sm:mb-8 sm:max-w-md">
        <div class="relative flex items-center sm:py-1">

            <x-heroicon-o-magnifying-glass class="absolute z-50 left-5 size-5"/>

            <x-text-input
                x-ref="searchInput"
                x-init="if ($wire.focusInput) $refs.searchInput.focus()"
                wire:model.live.debounce.500ms="query"
                name="q"
                placeholder="Search for users..."
                class="w-full rounded-none sm:!rounded-2xl !bg-slate-950 !bg-opacity-80 py-3 pl-14"
            />
        </div>
    </div>

    @if ($users->isEmpty())
        <section class="rounded-lg">
            <p class="my-8 text-lg text-center text-slate-500">No users found.</p>
        </section>
    @else
        <section class="max-w-2xl">
            <ul class="flex flex-col gap-2">
                @foreach ($users as $user)
                    <li>
                        <a
                            href="{{ route('profile.show', ['username' => $user->username]) }}"
                            class="flex items-center gap-3 p-4 transition-colors border group rounded-2xl border-slate-900 bg-slate-950 bg-opacity-80 hover:bg-slate-900"
                            wire:navigate
                        >
                            <figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12 flex-shrink-0 overflow-hidden bg-slate-800 transition-opacity group-hover:opacity-90">
                                <img
                                    class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-12 w-12"
                                    src="{{ $user->avatar_url }}"
                                    alt="{{ $user->username }}"
                                />
                            </figure>
                            <div class="flex flex-col overflow-hidden text-sm">
                                <div class="flex items-center space-x-2">
                                    <p class="font-medium truncate">
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
                                </div>
                                <p class="truncate transition-colors text-slate-500 group-hover:text-slate-400">
                                    {{ '@'.$user->username }}
                                </p>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
