@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-slate-500 cursor-not-allowed dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                rel="prev"
                wire:navigate
                class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-slate-700 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-900 focus:border-pink-500 focus:outline-none focus:ring ring-slate-300 active:bg-slate-100 active:text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white dark:active:bg-slate-700 dark:active:text-slate-300"
            >
                {!! __('pagination.previous') !!}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                rel="next"
                wire:navigate
                class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-slate-700 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-900 focus:border-pink-500 focus:outline-none focus:ring ring-slate-300 active:bg-slate-100 active:text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white dark:active:bg-slate-700 dark:active:text-slate-300"
            >
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-slate-500 cursor-not-allowed dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
