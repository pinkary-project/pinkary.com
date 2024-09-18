@props(['user'])

<a
    href="{{ route('profile.show', ['username' => $user->username]) }}"
    class="group flex items-center gap-3 px-4"
    wire:navigate
>
    <figure class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 dark:bg-slate-800 bg-slate-200 transition-opacity group-hover:opacity-90">
        <img
            src="{{ $user->avatar_url }}"
            alt="{{ $user->username }}"
            class="{{ $user->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
        />
    </figure>

    <div class="overflow-hidden text-sm">
        <div class="flex">
            <p class="truncate font-medium dark:text-slate-50 text-slate-950">
                {{ $user->name }}
            </p>

            @if ($user->is_verified && $user->is_company_verified)
                <x-icons.verified-company
                    :color="$user->right_color"
                    class="ml-1 mt-0.5 h-3.5 w-3.5"
                />
            @elseif ($user->is_verified)
                <x-icons.verified
                    :color="$user->right_color"
                    class="ml-1 mt-0.5 h-3.5 w-3.5"
                />
            @endif
        </div>

        <p class="truncate text-slate-500 transition-colors dark:group-hover:text-slate-400 group-hover:text-slate-600">
            {{ '@'.$user->username }}
        </p>
    </div>
</a>
