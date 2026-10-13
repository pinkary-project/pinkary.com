@php
    $accounts = App\Services\Accounts::all();
@endphp

<div class="space-y-1 border-t border-slate-200/70 pt-2 dark:border-slate-800/40">
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
            <div class="group flex items-center justify-between rounded-xl hover:bg-slate-100 dark:hover:bg-[#11192b]">
                <form method="POST" action="{{ route('accounts.switch', $account->username) }}" class="min-w-0 flex-1">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center gap-2 px-3 py-2 text-start text-sm font-medium text-slate-600 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white"
                    >
                        <img
                            src="{{ $account->avatar_url }}"
                            alt="{{ $account->username }}"
                            class="{{ $account->is_company_verified ? 'rounded-md' : 'rounded-full' }} size-6 shrink-0"
                        />
                        <span class="truncate">{{ '@' . $account->username }}</span>
                    </button>
                </form>

                <div class="shrink-0 pr-2">
                    <button
                        type="button"
                        x-on:click="
                            $dispatch('open-modal', 'confirm-remove-account');
                            $dispatch('set-remove-account', {
                                username: '{{ $account->username }}',
                                action: '{{ route('accounts.remove', $account->username) }}'
                            });
                        "
                        title="{{ __('Log out of @:username', ['username' => $account->username]) }}"
                        class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600 dark:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                    >
                        <x-heroicon-o-x-mark class="size-4" />
                        <span class="sr-only">{{ __('Log out of @:username', ['username' => $account->username]) }}</span>
                    </button>
                </div>
            </div>
        @endif
    @endforeach

    <a
        href="{{ route('login') }}"
        class="flex items-center gap-2 rounded-xl px-3 py-2 text-start text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-[#11192b] dark:hover:text-white"
    >
        <div class="flex size-6 shrink-0 items-center justify-center">
            <x-heroicon-o-plus class="size-4 text-slate-400 dark:text-slate-500" />
        </div>
        <span>{{ __('Add an existing account') }}</span>
    </a>
</div>
