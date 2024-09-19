<section>
    <header>
        <h2 class="text-lg font-medium dark:text-slate-400 text-slate-600">
            <div class="items flex items-center space-x-3">
                <h2 class="text-lg font-medium dark:text-slate-400 text-slate-600">
                    {{ $user->is_verified ? __('Manage Verified Badge') : __('Get Verified') }}
                </h2>
                <x-icons.verified
                    :color="$user->is_verified ? $user->right_color : 'gray'"
                    class="size-6"
                />
            </div>
        </h2>

        <div class="mt-2 text-sm text-slate-500">
            <span>Current status:</span>

            <span class="font-semibold">
                @if ($user->github_username === null && $user->is_verified === true)
                    {{ __('You are a verified user. You have access to the verified badge.') }}
                @elseif ($user->github_username === null)
                    {{ __('GitHub account not connected.') }}
                @elseif ($user->is_verified === false)
                    {{ __('GitHub account (@:username) connected but not sponsoring Pinkary.', ['username' => $user->github_username]) }}
                @else
                    {{ __('GitHub account (@:username) connected and sponsoring Pinkary. You are a verified user, as such you have access to the verified badge.', ['username' => $user->github_username]) }}
                @endif
            </span>

            @if ($user->is_verified === false)
                @if ($user->github_username === null)
                    <span class="text-sm text-slate-500">
                        {{ __('Get verified to show others that you are a trusted user. To get started, connect your GitHub account.') }}
                    </span>
                @else
                    <span class="text-sm text-slate-500">
                        {{ __('As the next step, sponsor Pinkary (via its creator, Nuno Maduro) with at least $9/month to get verified.') }}
                    </span>
                @endif
            @endif
        </div>
    </header>

    <div class="pb-6 pt-2">
        <div class="mt-6 space-y-6">
            @if ($user->github_username === null && $user->is_verified)
                <div class="items flex"></div>
            @elseif ($user->is_verified)
                <div class="flex items-center gap-4">
                    <a
                        href="{{ route('sponsors') }}"
                        target="_blank"
                    >
                        <x-primary-button>{{ __('Manage Sponsorship') }}</x-primary-button>
                    </a>

                    <form
                        method="post"
                        action="{{ route('profile.connect.github.destroy') }}"
                    >
                        @csrf
                        @method('delete')

                        <x-secondary-button type="submit">{{ __('Disconnect GitHub') }}</x-secondary-button>
                    </form>
                </div>
            @else
                @if ($user->github_username === null)
                    <div class="flex items-center gap-4">
                        <a href="{{ route('profile.connect.github') }}">
                            <x-primary-button>{{ __('Connect GitHub') }}</x-primary-button>
                        </a>
                    </div>
                @else
                    <div class="flex items-center gap-4">
                        <a
                            href="{{ route('sponsors') }}"
                            target="_blank"
                        >
                            <x-primary-button>{{ __('Sponsor Pinkary') }}</x-primary-button>
                        </a>

                        <form
                            method="post"
                            action="{{ route('profile.verified.update') }}"
                        >
                            @csrf

                            <x-secondary-button type="submit">
                                {{ __('Refresh Verified Eligibility') }}
                            </x-secondary-button>
                        </form>

                        <form
                            method="post"
                            action="{{ route('profile.connect.github.destroy') }}"
                        >
                            @csrf
                            @method('delete')

                            <x-secondary-button type="submit">{{ __('Disconnect GitHub') }}</x-secondary-button>
                        </form>
                    </div>
                @endif
            @endif
        </div>

        <x-input-error
            :messages="$errors->verified->get('github_username')"
            class="mt-2"
        />
    </div>
</section>
