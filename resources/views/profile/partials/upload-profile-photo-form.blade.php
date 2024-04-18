<section>
    <header>
        <h2 class="text-lg font-medium text-slate-400">
            {{ __('Profile Photo') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500 mb-4">
            {{ __("Upload a new avatar to your profile.") }}
        </p>
    </header>
        <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4">
            <div class="flex items-center justify-center">
                @if (auth()->user()->avatar)
                    <figure class="w-20 h-20 flex-shrink-0">
                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                             class="inline-block w-full h-full rounded-full object-cover">
                    </figure>
                @else
                    <span class="w-20 h-20 rounded-full bg-slate-500 flex items-center justify-center flex-shrink-0">
                        <x-icons.user class="w-6 h-6 text-slate-300"/>
                    </span>
                @endif
            </div>
            @if (auth()->user()->has_custom_avatar)
                <div class="flex gap-2">
                    <p class="text-sm text-slate-500">
                        {{ __("If you delete your uploaded avatar, your profile will revert to using default.") }}
                    </p>
                </div>
            @endif
        </div>

        <form method="post" enctype="multipart/form-data" action="{{ route('profile.avatar.update') }}">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="avatar" :value="__('Avatar')" class="sr-only" />
                <x-text-input
                    type="file"
                    accept="image/*"
                    id="avatar"
                    name="avatar"
                    class="mt-4 border block w-full text-sm focus:ring-0 focus:outline-none
                    file:mr-4 file:border-0 file:text-xs file:font-semibold file:bg-pink-200 file:transition-colors
                    file:text-pink-700 file:px-4 file:py-2 file:tracking-widest hover:file:bg-pink-100"
                />
                <x-input-error class="mt-2" :messages="$errors->get('avatar')"/>
            </div>

            <div class="flex items-center gap-2 mt-7">
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
