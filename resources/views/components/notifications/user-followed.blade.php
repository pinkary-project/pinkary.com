@props([
    'notification',
    'follower',
])

<div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
    <figure class="{{ $follower->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 shrink-0 bg-slate-100 transition-opacity group-hover:opacity-90 dark:bg-slate-800">
        <img
            src="{{ $follower->avatar_url }}"
            alt="{{ $follower->username }}"
            class="{{ $follower->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
        />
    </figure>
    <p>
        <span class="font-medium text-slate-950 dark:text-white">{{ '@' . $follower->username }}</span>
        followed you
    </p>
</div>
