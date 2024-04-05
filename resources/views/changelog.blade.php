<x-app-layout>
    <x-slot name="title">Changelog</x-slot>

    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-md px-2 sm:px-0 min-h-screen">

            <p class="text-slate-400">A changelog of the latest Pinkary feature releases, product updates and important bug fixes.</p>

            <div class="relative py-1 mt-12 mb-20">
                <div class="absolute left-0 top-0 bottom-0 hidden sm:flex w-6 justify-center">
                    <div class="w-px border-r border-dashed border-slate-700"></div>
                </div>

                <ul role="list" class="space-y-6 sm:space-y-12">
                    <livewire:changelog.releases />
                </ul>
            </div>
        </div>
    </div>


</x-app-layout>
