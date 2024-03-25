<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="sm:p-2">
                <a
                    href="{{ route('profile.show', ['user' => auth()->user()->username]) }}"
                    class="flex text-gray-400 hover:underline"
                    wire:navigate
                >
                    <x-icons.chevron-left class="h-6 w-6" />
                    <span>Back</span>
                </a>
            </div>

            <div class="p-4 shadow sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.verified-form')
                </div>
            </div>

            <div class="p-4 shadow sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 shadow sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 shadow sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
