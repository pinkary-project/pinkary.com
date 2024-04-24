<section>
    <header>
        <h2 class="text-lg font-medium text-slate-400">
            {{ __('Profile Photo') }}
        </h2>

        <p class="mb-4 mt-1 text-sm text-slate-500">
            {{ __('Upload a profile photo to personalize your account.') }}
        </p>
    </header>
    <div class="flex flex-col items-center gap-2 sm:flex-row sm:gap-4">
        <div class="flex items-center justify-center">
            <figure class="h-20 w-20 flex-shrink-0">
                <img
                    src="{{ auth()->user()->avatar_url }}"
                    alt="{{ auth()->user()->name }}"
                    class="inline-block h-full w-full rounded-full object-cover"
                />
            </figure>
        </div>
        @if (auth()->user()->is_uploaded_avatar)
            <div class="flex gap-2">
                <p class="text-sm text-slate-500">
                    {{ __('If you delete your uploaded avatar, we will try to fetch your image using our avatar service') }}
                </p>
            </div>
        @else
            <div class="flex gap-2">
                <p class="text-sm text-slate-500">
                    @php($message = auth()->user()->github_username ? 'GitHub or' : '')
                    {{ __("You can click the button below to fetch your avatar from your {$message} Gravatar account") }}
                </p>
            </div>
        @endif
    </div>

    <form
        method="post"
        enctype="multipart/form-data"
        action="{{ route('profile.avatar.update') }}"
    >
        @csrf
        @method('patch')

        <div>
            <x-input-label
                for="avatar"
                :value="__('Avatar')"
                class="sr-only"
            />
            <x-text-input
                type="file"
                accept="image/*"
                id="avatar"
                name="avatar"
                class="mt-4 block w-full border text-sm file:mr-4 file:border-0 file:bg-pink-200 file:px-4 file:py-2 file:text-xs file:font-semibold file:tracking-widest file:text-pink-700 file:transition-colors hover:file:bg-pink-100 focus:outline-none focus:ring-0"
            />
            <x-input-error
                class="mt-2"
                :messages="$errors->get('avatar')"
            />
        </div>

        <div class="mt-7 flex items-center gap-2">
            <x-primary-button>{{ __('Upload') }}</x-primary-button>
        </div>
    </form>
    <div class="relative">
        @if (auth()->user()->is_uploaded_avatar)
            <form
                method="post"
                class="absolute left-[100px] -top-[34px]"
                action="{{ route('profile.avatar.delete') }}"
            >
                @csrf
                @method('delete')

                <x-secondary-button type="submit">
                    {{ __('Delete Uploaded Avatar') }}
                </x-secondary-button>
            </form>
        @else
            <div class="absolute left-[100px] -top-[34px]">
                <x-secondary-button
                    x-data="{ fetchAvatar: function () { window.location.href = '{{ route('profile.avatar.fetch') }}' } }"
                    @click.prevent="fetchAvatar"
                >
                    {{ __('Fetch Avatar') }}
                </x-secondary-button>
            </div>
        @endif
    </div>
</section>
