<footer class="border-t border-gray-800">
    <div class="mx-auto max-w-7xl overflow-hidden px-6 py-16 sm:py-24 lg:px-8">
        <nav
            class="-mb-6 columns-2 sm:flex sm:justify-center sm:space-x-12"
            aria-label="Footer"
        >
            <div class="pb-6">
                <a
                    href="{{ route('changelog') }}"
                    class="text-sm leading-6 text-slate-400 hover:text-slate-200"
                    >Changelog</a
                >
            </div>
            <div class="pb-6">
                <a
                    href="{{ route('terms') }}"
                    class="text-sm leading-6 text-slate-400 hover:text-slate-200"
                    >Terms</a
                >
            </div>
            <div class="pb-6">
                <a
                    href="{{ route('privacy') }}"
                    class="text-sm leading-6 text-slate-400 hover:text-slate-200"
                    >Privacy Policy</a
                >
            </div>
            <div class="pb-6">
                <a
                    href="{{ route('support') }}"
                    class="text-sm leading-6 text-slate-400 hover:text-slate-200"
                    >Support</a
                >
            </div>
            <div class="pb-6">
                <a
                    href="{{ route('brand.resources') }}"
                    class="text-sm leading-6 text-slate-400 hover:text-slate-200"
                    >Brand</a
                >
            </div>
        </nav>

        <div class="mt-10 flex space-x-10 sm:justify-center">
            <a
                href="https://twitter.com/PinkaryProject"
                target="_blank"
                class="text-slate-400 hover:text-slate-200"
            >
                <span class="sr-only">X</span>
                <svg
                    class="h-6 w-6"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="M13.6823 10.6218L20.2391 3H18.6854L12.9921 9.61788L8.44486 3H3.2002L10.0765 13.0074L3.2002 21H4.75404L10.7663 14.0113L15.5685 21H20.8131L13.6819 10.6218H13.6823ZM11.5541 13.0956L10.8574 12.0991L5.31391 4.16971H7.70053L12.1742 10.5689L12.8709 11.5655L18.6861 19.8835H16.2995L11.5541 13.096V13.0956Z" />
                </svg>
            </a>

            <a
                href="https://github.com/pinkary-project"
                target="_blank"
                class="text-slate-400 hover:text-slate-200"
            >
                <span class="sr-only">GitHub</span>

                <x-icons.github class="h-6 w-6" />
            </a>
        </div>

        <p class="mt-10 text-xs leading-5 text-slate-400 sm:text-center">&copy; {{ date('Y') }} {{ config('app.name') }}.</p>
        <p class="text-xs leading-5 text-slate-400 sm:text-center">{{ $version }}</p>
    </div>
</footer>
