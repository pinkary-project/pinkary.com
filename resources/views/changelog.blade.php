<x-app-layout>
    <x-slot name="title">Changelog</x-slot>

    <div class="mx-auto w-full max-w-176 px-4 pb-20">
        <p class="text-sm text-slate-600 sm:text-base dark:text-slate-400">
            A changelog of the latest Pinkary feature releases, product updates and important bug fixes.
        </p>

        <div class="relative mt-10">
            <div class="absolute top-3 bottom-3 left-0 hidden w-6 justify-center sm:flex" aria-hidden="true">
                <div class="w-px bg-slate-200 dark:bg-slate-800"></div>
            </div>

            <ul role="list" class="space-y-6 sm:space-y-8">
                @foreach ($releases as $version => $release)
                    <li class="relative flex items-start">
                        <div class="relative mt-4 mr-4 hidden size-6 shrink-0 items-center justify-center sm:flex">
                            <div class="size-2 rounded-full bg-pink-500 ring-4 ring-slate-100 dark:ring-[#060c18]"></div>
                        </div>

                        <div class="relative flex-1 overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 p-4 shadow-xs transition hover:border-slate-300 sm:p-5 dark:border-slate-800/60 dark:bg-[#07101f]/90 dark:hover:border-slate-700/60">
                            <header class="flex w-full flex-1 items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800/60">
                                <h2 class="font-mona font-bold text-slate-950 dark:text-white">
                                    Version {{ $version }}
                                </h2>

                                <time
                                    datetime="{{ $release['publishedAt'] }}"
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ $release['publishedAt'] }}
                                </time>
                            </header>

                            @if ($release['changes'])
                                <div class="mt-3.5">
                                    <h3 class="text-xs font-semibold tracking-wider text-slate-400 uppercase dark:text-slate-500">
                                        Improvements &amp; Bug fixes
                                    </h3>

                                    <ul role="list" class="mt-3 space-y-2">
                                        @foreach ($release['changes'] as $change)
                                            <li class="flex items-start gap-2.5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                                                <span class="mt-2 size-1.5 shrink-0 rounded-full bg-pink-500"></span>
                                                <span>{{ $change }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>
