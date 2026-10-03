@props(['content', 'url'])

<div class="mt-1" x-data="feedContent">
    <div class="answer max-h-48 overflow-hidden break-words text-slate-700 dark:text-slate-200" x-ref="text">
        {!! $content['html'] !!}
    </div>
    <a
        x-cloak
        x-show="overflowing"
        href="{{ $url }}"
        wire:navigate
        data-navigate-ignore="true"
        class="mt-2 inline-block text-sm font-medium text-pink-500 hover:underline"
    >
        {{ __('View more') }}
    </a>

    @if ($content['images'] !== [])
        <div
            class="mt-3 flex snap-x snap-mandatory gap-2 overflow-x-auto rounded-xl"
            x-data="hasLightBoxImages"
            data-navigate-ignore="true"
            role="group"
            aria-label="{{ __('Post images') }}"
            tabindex="0"
        >
            @foreach ($content['images'] as $image)
                <figure @class(['flex shrink-0 snap-center items-center justify-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-900', 'w-full' => count($content['images']) === 1, 'w-[85%]' => count($content['images']) > 1])>
                    <img
                        src="{{ $image['src'] }}"
                        alt="{{ $image['alt'] }}"
                        class="max-h-96 w-full object-contain"
                        loading="lazy"
                    />
                </figure>
            @endforeach
        </div>
        @if (count($content['images']) > 1)
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ __('Swipe to see all images. Tap an image to enlarge it.') }}
            </p>
        @endif
    @elseif ($content['preview'] !== '')
        <div data-navigate-ignore="true">{!! $content['preview'] !!}</div>
    @endif
</div>
