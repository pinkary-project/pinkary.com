<div class="mb-12 w-full text-slate-200">
    <div class="mb-8 w-full max-w-md">
        <div class="relative flex items-center py-1">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="absolute left-5 z-50 size-5"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
            </svg>

            <x-text-input
                x-ref="searchInput"
                x-init="if ($wire.focusInput) $refs.searchInput.focus()"
                wire:model.live.debounce.500ms="query"
                name="q"
                placeholder="Search for users..."
                class="w-full !rounded-2xl !bg-slate-950 !bg-opacity-80 py-3 pl-14"
            />
        </div>
    </div>

    @if ($users->isEmpty())
        <section class="rounded-lg">
            <p class="my-8 text-center text-lg text-slate-500">No users found.</p>
        </section>
    @else
        <section class="max-w-2xl">
            <ul class="flex flex-col gap-2">
                @foreach ($users as $user)
                    <li>
                        <a
                            href="{{ route('profile.show', ['username' => $user->username]) }}"
                            wire:navigate
                        >
                            <x-user-tile :$user />
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
