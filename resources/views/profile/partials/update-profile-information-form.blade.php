@php
    use App\Enums\UserMailPreference;
    use Illuminate\Contracts\Auth\MustVerifyEmail;
@endphp

<section>
    <header>
        <h2 class="text-lg font-medium dark:text-slate-400 text-slate-600">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form
        id="send-verification"
        method="post"
        action="{{ route('verification.send') }}"
    >
        @csrf
    </form>

    <form
        method="post"
        action="{{ route('profile.update') }}"
        class="mt-6 space-y-6"
    >
        @csrf
        @method('patch')

        <div>
            <x-input-label
                for="name"
                :value="__('Name')"
            />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $user->name)"
                required
                autocomplete="name"
            />
            <x-input-error
                class="mt-2"
                :messages="$errors->get('name')"
            />
        </div>

        <div>
            <x-input-label
                for="username"
                :value="__('Username')"
            />
            <x-text-input
                id="username"
                name="username"
                type="text"
                class="mt-1 block w-full"
                :value="old('username', $user->username)"
                required
                autocomplete="username"
            />
            <x-input-error
                class="mt-2"
                :messages="$errors->get('username')"
            />
        </div>

        <div>
            <x-input-label
                for="email"
                :value="__('Email')"
            />
            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full"
                :value="old('email', $user->email)"
                required
                autocomplete="username"
            />
            <x-input-error
                class="mt-2"
                :messages="$errors->get('email')"
            />

            @if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ __('Your email address is unverified.') }}

                        <button
                            form="send-verification"
                            class="rounded-md text-sm text-slate-500 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                </div>
            @endif
        </div>

        <div>
            <x-input-label
                for="bio"
                :value="__('Bio')"
            />
            <x-text-input
                id="bio"
                name="bio"
                type="text"
                class="mt-1 block w-full"
                :value="old('bio', $user->bio)"
                required
                autocomplete="bio"
            />
            <x-input-error
                class="mt-2"
                :messages="$errors->get('bio')"
            />
        </div>

        <div>
            <x-input-label
                for="mail_preference_time"
                :value="__('Mail Preference Time')"
            />
            <x-select-input
                id="mail_preference_time"
                name="mail_preference_time"
                class="mt-1 block w-full"
                :options="UserMailPreference::toArray()"
                :value="old('mail_preference_time', $user->mail_preference_time->value)"
                required
                autocomplete="mail_preference_time"
            />
            <x-input-error
                class="mt-2"
                :messages="$errors->get('mail_preference_time')"
            />
        </div>

        <div>
            <x-input-label
                for="prefers_anonymous_questions"
                :value="__('How would you like to do questions by default?')"
            />
            <x-select-input
                id="prefers_anonymous_questions"
                name="prefers_anonymous_questions"
                class="mt-1 block w-full"
                :options="[true => 'Anonymously', false => 'Publicly']"
                :value="old('prefers_anonymous_questions', $user->prefers_anonymous_questions)"
                required
            />
            <x-input-error
                class="mt-2"
                :messages="$errors->get('prefers_anonymous_questions')"
            />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>
