@php
    use App\Rules\ValidTimezone;use Illuminate\Contracts\Auth\MustVerifyEmail;
@endphp

<section>
    <header>
        <h2 class="text-lg font-medium text-slate-400">
            {{ __('Account Settings') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __("Update your account's settings such as your questions preferences.") }}
        </p>
    </header>


    <form method="post" action="{{ route('account.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="questions_preference" :value="__('Questions Preference')"/>
            <x-select-input
                id="questions_preference"
                name="questions_preference"
                class="mt-1 block w-full"
                :options="['anonymously' => 'Anonymously', 'public' => 'Public']"
                :value="old('questions_preference', $user->questions_preference)"
                required
                autocomplete="questions_preference"
            />
            <x-input-error class="mt-2" :messages="$errors->get('questions_preference')"/>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>
