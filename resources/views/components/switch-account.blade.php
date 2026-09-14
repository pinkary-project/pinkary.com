@php
    $accounts = App\Services\Accounts::all();
@endphp

<div class="border-t border-slate-200/70 pt-2 dark:border-slate-800/40">
    <div class="px-3 py-1 text-xs font-semibold tracking-wider text-slate-400 uppercase dark:text-slate-500">
        {{ __('Accounts') }}
    </div>

    @foreach ($accounts as $account)
        @if ($account->is(auth()->user()))
            <div class="flex items-center justify-between rounded-xl bg-slate-100 px-3 py-2 text-sm font-medium text-slate-900 dark:bg-slate-800/60 dark:text-white">
                <div class="flex min-w-0 items-center gap-2">
                    <img
                        src="{{ $account->avatar_url }}"
                        alt="{{ $account->username }}"
                        class="{{ $account->is_company_verified ? 'rounded-md' : 'rounded-full' }} size-6 shrink-0"
                    />
                    <span class="truncate">{{ '@' . $account->username }}</span>
                </div>
                <x-heroicon-o-check class="size-4 shrink-0 text-pink-500" />
            </div>
        @else
            <form method="POST" action="{{ route('accounts.switch', $account->username) }}">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-start text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-[#11192b] dark:hover:text-white"
                >
                    <div class="flex min-w-0 items-center gap-2">
                        <img
                            src="{{ $account->avatar_url }}"
                            alt="{{ $account->username }}"
                            class="{{ $account->is_company_verified ? 'rounded-md' : 'rounded-full' }} size-6 shrink-0"
                        />
                        <span class="truncate">{{ '@' . $account->username }}</span>
                    </div>
                </button>
            </form>
        @endif
    @endforeach

    <x-dropdown-link :href="route('login')">
        <div class="flex items-center gap-2">
            <x-heroicon-o-plus class="size-4 shrink-0" />
            <span>{{ __('Add an existing account') }}</span>
        </div>
    </x-dropdown-link>
</div>
