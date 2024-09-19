<section x-data='{
    avatar: null,
    errors: @json($errors->get('avatar')),
    checkFileSize(target) {
        const maxFileSize = 8 * 1024 * 1024;

        if ((target.files[0]?.size ?? 0) > maxFileSize) {
            this.errors = ["The avatar may not be greater than 2MB."];
            target.value = null;
            this.avatar = null;
        } else {
            this.errors = [];
            this.avatar = target.files[0];
        }
    }
}'>
    <header>
        <h2 class="text-lg font-medium dark:text-slate-400 text-slate-600">
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
                    alt="{{ auth()->user()->name }}"
                    :src="avatar ? URL.createObjectURL(avatar) : '{{ auth()->user()->avatar_url }}'"
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
                x-on:change="checkFileSize($event.target)"
                class="mt-4 block w-full border text-sm file:mr-4 file:border-0 file:bg-pink-200 file:px-4 file:py-2 file:text-xs file:font-semibold file:tracking-widest file:text-pink-700 file:transition-colors hover:file:bg-pink-100 focus:outline-none focus:ring-0"
            />
            <div x-show="errors.length > 0" class="mt-4">
                <template x-for="(error, index) in errors" :key="index">
                    <span class="mt-2 text-sm text-red-600" x-text="error"></span>
                </template>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2">
            <x-primary-button>{{ __('Upload') }}</x-primary-button>
        </div>
    </form>
    <div class="relative">
        <form
            method="post"
            class="absolute -top-[34px] left-[100px]"
            action="{{ route('profile.avatar.destroy') }}"
        >
            @csrf
            @method('delete')

            <x-secondary-button type="submit">
                {{ auth()->user()->is_uploaded_avatar ? __('Delete Uploaded Avatar') : __('Re-fetch Avatar') }}
            </x-secondary-button>
        </form>
    </div>
</section>
