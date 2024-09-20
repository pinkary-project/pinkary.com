<x-app-layout>
    <x-slot name="title">Changelog</x-slot>

    <div class="flex flex-col items-center justify-center">
        <div class="min-h-screen w-full max-w-md px-2 sm:px-0">
            <p class="dark:text-slate-400 text-slate-600">A changelog of the latest Pinkary feature releases, product updates and important bug fixes.</p>

            <div class="relative mb-20 mt-12 py-1">
                <div class="absolute bottom-0 left-0 top-0 hidden w-6 justify-center sm:flex">
                    <div class="w-px border-r border-dashed dark:border-slate-700 border-slate-300"></div>
                </div>

                <ul
                    role="list"
                    class="space-y-6 sm:space-y-12"
                >
                    @foreach ($releases as $version => $release)
                        <li class="relative flex">
                            <div class="relative mr-4 mt-3.5 hidden size-6 flex-none items-center justify-center sm:flex">
                                <div class="size-1.5 rounded-full bg-pink-500 ring-1 ring-pink-500"></div>
                            </div>

                            <div class="relative flex-1 overflow-hidden rounded-xl border dark:bg-transparent bg-slate-100 dark:border-slate-900 border-slate-200 dark:shadow-none shadow-sm shadow-slate-200">
                                <header class="flex w-full flex-1 items-center justify-between border-b dark:border-slate-900 border-slate-200 px-4 py-3.5 dark:text-slate-200 text-slate-800">
                                    <h2 class="font-bold">Version {{ $version }}</h2>
                                    <time
                                        datetime="{{ $release['publishedAt'] }}"
                                        class="flex-none py-0.5 text-xs dark:font-semibold leading-5 dark:text-slate-500 text-slate-600"
                                    >
                                        {{ $release['publishedAt'] }}
                                    </time>
                                </header>
                                <div class="prose prose-sm dark:prose-invert px-4 py-3.5 prose-h3:text-sm">
                                    @if ($release['changes'])
                                        <h3>Improvements & Bug fixes</h3>
                                        <ul>
                                            @foreach ($release['changes'] as $change)
                                                <li>{{ $change }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div class="absolute -right-[200px] -top-[200px] z-10 transform-gpu opacity-20 blur-3xl">
                                    <div
                                        class="size-[600px] bg-gradient-to-r dark:from-pink-900 from-pink-50 dark:to-pink-500 to-pink-200"
                                        style="
                                            clip-path: polygon(
                                                50% 0%,
                                                61% 35%,
                                                98% 35%,
                                                68% 57%,
                                                79% 91%,
                                                50% 70%,
                                                21% 91%,
                                                32% 57%,
                                                2% 35%,
                                                39% 35%
                                            );
                                        "
                                    ></div>
                                </div>

                                <div class="absolute inset-x-0 -bottom-2 flex h-2 justify-center overflow-hidden">
                                    <div class="absolute right-5 -mt-px flex h-[2px] w-2/3">
                                        <div class="w-full flex-none bg-gradient-to-r from-slate-950 via-pink-400 to-slate-950 blur-sm"></div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
