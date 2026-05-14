@props(['user'])

<a
    href="{{ route('profile.show', ['username' => $user->username]) }}"
    class="group flex items-center gap-3"
    wire:navigate
>
    <figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-100 transition-opacity group-hover:opacity-90 dark:bg-slate-800">
        <img
            src="{{ $user->avatar_url }}"
            alt="{{ $user->username }}"
            class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
        />
    </figure>

    <div class="min-w-0 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
        <p class="truncate font-medium text-slate-950 dark:text-white">
            {{ $user->name }}
        </p>

        @if ($user->is_verified && $user->is_company_verified)
            <x-icons.verified-company
                :color="$user->right_color"
                class="h-3.5 w-3.5"
            />
        @elseif ($user->is_verified)
            <x-icons.verified
                :color="$user->right_color"
                class="h-3.5 w-3.5"
            />
        @endif

        <p class="truncate text-slate-500 transition-colors group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-300">
            {{ '@'.$user->username }}
        </p>
    </div>
</a>
