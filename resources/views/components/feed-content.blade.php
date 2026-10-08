@props(['content'])

<div class="mt-1" x-data="feedContent">
    <div
        class="answer overflow-hidden wrap-anywhere text-slate-700 dark:text-slate-200"
        x-bind:class="{ 'max-h-48': ! expanded, 'max-h-none': expanded }"
        x-ref="text"
    >
        {!! $content['html'] !!}
    </div>
    <button
        type="button"
        x-cloak
        x-show="overflowing && ! expanded"
        x-on:click="expanded = true"
        data-navigate-ignore="true"
        class="mt-2 inline-block text-sm font-medium text-pink-500 hover:underline"
    >
        {{ __('View more') }}
    </button>

    @if ($content['images'] !== [])
        <div
            class="relative mt-2"
            x-data="imageGallery"
            x-bind:style="{ '--gallery-height': height + 'px' }"
            data-navigate-ignore="true"
            role="group"
            aria-label="{{ __('Post images') }}"
        >
            <div
                x-ref="viewport"
                class="scrollbar-none overflow-x-auto overscroll-x-contain rounded-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-500"
                x-bind:class="{
                    'cursor-grabbing select-none [&_img]:cursor-grabbing': dragging,
                    'cursor-grab [&_img]:cursor-grab': ! dragging && (canPrevious || canNext),
                }"
                tabindex="0"
                aria-label="{{ __('Drag or scroll post images. Use the left and right arrow keys to navigate.') }}"
                x-on:keydown.right.prevent.stop="scroll(1)"
                x-on:keydown.left.prevent.stop="scroll(-1)"
                x-on:scroll.passive="updateControls()"
                x-on:load.capture="measure()"
                x-on:pointerdown="startDrag($event)"
                x-on:pointermove="drag($event)"
                x-on:pointerup="endDrag($event)"
                x-on:pointercancel="endDrag($event)"
                x-on:lostpointercapture="endDrag($event)"
                x-on:dragstart.prevent
                x-on:click.capture="preventDragClick($event)"
            >
                <div class="flex h-[var(--gallery-height)] w-max gap-2" x-data="hasLightBoxImages">
                    @foreach ($content['images'] as $image)
                        <figure class="h-full shrink-0 overflow-hidden rounded-xl">
                            <img
                                src="{{ $image['src'] }}"
                                alt="{{ $image['alt'] }}"
                                class="h-full w-auto max-w-none"
                                loading="lazy"
                                draggable="false"
                            />
                        </figure>
                    @endforeach
                </div>
            </div>
            @if (count($content['images']) > 1)
                <span
                    class="pointer-events-none absolute top-2 right-2 rounded-full bg-slate-950/70 px-2.5 py-1 text-xs font-medium text-white tabular-nums"
                    role="status"
                    aria-label="{{ __('Image :current of :total', ['current' => 1, 'total' => count($content['images'])]) }}"
                    x-bind:aria-label="@js(__('Image :current of :total')).replace(':current', currentImage).replace(':total', {{ count($content['images']) }})"
                    x-text="currentImage + '/{{ count($content['images']) }}'"
                >1/{{ count($content['images']) }}</span>
            @endif
        </div>
    @elseif ($content['preview'] !== '')
        <div data-navigate-ignore="true">{!! $content['preview'] !!}</div>
    @endif
</div>
