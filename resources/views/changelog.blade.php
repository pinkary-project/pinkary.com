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
                    <li class="relative flex">
                        <div class="relative mr-4 mt-3.5 hidden size-6 flex-none items-center justify-center sm:flex">
                            <div class="size-1.5 rounded-full bg-pink-500 ring-1 ring-pink-500"></div>
                        </div>

                        <div class="relative flex-1 overflow-hidden rounded-xl border border-slate-900">
                            <div class="absolute -right-[200px] -top-[200px] z-10 transform-gpu opacity-20 blur-3xl">
                                <div
                                    class="size-[600px] bg-gradient-to-r from-pink-900 to-pink-500"
                                    style="clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)"
                                ></div>
                            </div>
                            <header class="flex w-full flex-1 items-center justify-between border-b border-slate-900 px-4 py-3.5 text-slate-200">
                                <h2 class="font-bold">Version 1.5</h2>
                                <time datetime="2023-01-23T10:32" class="flex-none py-0.5 text-xs font-semibold leading-5 text-slate-500">
                                    April 10, 2024
                                </time>
                            </header>
                            <div class="prose prose-sm prose-invert px-4 py-3.5 prose-h3:text-sm">
                                <h3>Improvements & Changes</h3>
                                <ul>
                                    <!-- added changelog page -->
                                    <li>
                                        Add a changelog page to keep track of the latest Pinkary feature releases, product updates and important bug
                                        fixes.
                                    </li>
                                </ul>
                            </div>

                            <div class="absolute inset-x-0 -bottom-2 flex h-2 justify-center overflow-hidden">
                                <div class="absolute right-5 -mt-px flex h-[2px] w-2/3">
                                    <div class="w-full flex-none bg-gradient-to-r from-slate-950 via-pink-400 to-slate-950 blur-sm"></div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
