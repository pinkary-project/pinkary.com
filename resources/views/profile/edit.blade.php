<x-app-layout>
    <div class="border-b border-r border-slate-800/30 bg-[#07101f]/95 px-6 py-6">
        <div class="mx-auto max-w-2xl space-y-6">
            <div>
                <a
                    href="{{ route('profile.show', ['username' => auth()->user()->username]) }}"
                    class="flex items-center space-x-1 dark:text-slate-400 text-slate-600 hover:underline"
                    wire:navigate
                >
                    <x-icons.chevron-left class="size-5" />
                    <span>Back</span>
                </a>
            </div>

            <div class="border border-slate-800/30 bg-[#0b1324] p-6">
                <div class="max-w-xl">
                    @include('profile.partials.verified-form')
                </div>
            </div>

            <div class="border border-slate-800/30 bg-[#0b1324] p-6">
                <div class="max-w-xl">
                    @include('profile.partials.upload-profile-photo-form')
                </div>
            </div>

            <div class="border border-slate-800/30 bg-[#0b1324] p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="border border-slate-800/30 bg-[#0b1324] p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="border border-slate-800/30 bg-[#0b1324] p-6">
                <div class="max-w-xl">
                    <livewire:profile.two-factor-authentication-form />
                </div>
            </div>

            <div class="border border-slate-800/30 bg-[#0b1324] p-6">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
