<section>
    <header>
        <h2 class="text-lg font-medium text-slate-400">
            {{ __('Profile Photo') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __("Upload a new avatar to your profile.") }}
        </p>
    </header>
        <div class="grid grid-cols-1 sm:gap-4 sm:grid-cols-12">
            <div class="sm:col-span-2 min-w-16 w-16 max-w-16 flex items-center justify-center my-4">
                @if (auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                         class="w-16 h-16 rounded-full object-cover">
                @else
                    <span class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center">
                    <x-icons.user class="w-8 h-8 text-gray-400"/>
                </span>
                @endif
            </div>
            @if (auth()->user()->has_custom_avatar)
                <div class="sm:col-span-10 flex items-center justify-self-start gap-2">
                    <p class="mt-1 text-sm text-slate-500">
                        {{ __("If you delete your uploaded avatar, your profile will revert to using our avatar service & fetch one from your links.") }}
                    </p>
                </div>
            @endif
        </div>

        <form method="post" enctype="multipart/form-data" action="{{ route('profile.avatar.update') }}">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="avatar" :value="__('Avatar')"/>
                <x-text-input
                    type="file"
                    accept="image/*"
                    id="avatar"
                    name="avatar"
                    class="my-4 border block w-full text-sm focus:ring-0 focus:outline-none
                    file:mr-4 file:border-0 file:text-sm file:font-semibold file:bg-pink-200
                    file:text-pink-700 file:px-4 file:py-2 file:rounded-md hover:file:bg-pink-100"
                />
                <x-input-error class="my-2" :messages="$errors->get('avatar')"/>
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Upload') }}</x-primary-button>
            </form>
                @if (auth()->user()->has_custom_avatar)
                <form method="post" action="{{ route('profile.avatar.delete') }}">
                    @csrf
                    @method('delete')

                    <x-secondary-button type="submit">
                        {{ __('Delete Uploaded Avatar') }}
                    </x-secondary-button>
                </form>
                @endif
            </div>
</section>
