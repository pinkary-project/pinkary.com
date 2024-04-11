<x-app-layout>
    <x-slot name="title">Changelog</x-slot>

    <div class="flex flex-col items-center justify-center">
        <div class="min-h-screen w-full max-w-md px-2 sm:px-0">
            <p class="text-slate-400">A changelog of the latest Pinkary feature releases, product updates and important bug fixes.</p>

            <div class="relative mb-20 mt-12 py-1">
                <div class="absolute bottom-0 left-0 top-0 hidden w-6 justify-center sm:flex">
                    <div class="w-px border-r border-dashed border-slate-700"></div>
                </div>

                <ul role="list" class="space-y-6 sm:space-y-12">
                    <x-changelog.releases />
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
