<x-app-layout>
    <div class="mx-auto my-16 max-w-7xl px-6 lg:px-8">
        <a
            href="{{ route('about') }}"
            class="-mt-10 mb-12 flex items-center text-slate-400 hover:underline"
            wire:navigate
        >
            <x-icons.chevron-left class="size-4" />
            <span>Back</span>
        </a>

        <div class="mt-6">
            <div class="prose prose-slate prose-invert mx-auto max-w-4xl">
                <h1>Support</h1>
                <p><strong>Last Updated: 02 March 2024</strong></p>

                <p>
                    If you have any questions or need help, please feel free to contact us at <a href="mailto:team@pinkary.com">team@pinkary.com</a> .
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
