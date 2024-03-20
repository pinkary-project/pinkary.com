<x-app-layout>
    <div class="mx-auto mb-8 max-w-7xl sm:px-6 lg:px-8">
        <div class="sm:p-2">
            <a href="{{ route('welcome') }}" class="flex text-gray-400 hover:underline">
                <x-icons.chevron-left class="h-6 w-6" />
                <span>Back</span>
            </a>
        </div>

        <div class="overflow-hidden sm:rounded-lg">
            <div class="p-6 sm:px-20">
                <div class="text-2xl">Support</div>

                <div class="mt-6 text-gray-500">
                    <div class="mx-auto max-w-4xl text-sm text-gray-500">
                        <h1 class="my-4 text-center text-3xl font-bold">Support</h1>
                        <p><strong>Last Updated: 02 March 2024</strong></p>

                        <p class="mt-4">
                            If you have any questions or need help, please feel free to contact us at
                            <a href="mailto:team@pinkary.com">team@pinkary.com</a>
                            .
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
