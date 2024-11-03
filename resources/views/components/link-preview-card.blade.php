<div
    id="link-preview-card"
    data-url="{{ $url }}"
    class="mx-auto mt-2 min-w-full group/preview" data-navigate-ignore="true"
>
@if ($data->has('html'))
    <div class="w-full overflow-hidden rounded-lg border border-slate-300 dark:border-0">
        {!! $data->get('html') !!}
    </div>
@elseif($data->has('image'))
    @php($shortUrl = parse_url($url)['host'])
    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">
        <div
            title="Click to visit: {{ $shortUrl }}"
            class="relative w-full bg-slate-100/90 border border-slate-300
            dark:border-0 rounded-lg dark:group-hover/preview:border-0 overflow-hidden">
                    <img
                        src="{{ $data->get('image') }}"
                        alt="{{ $data->get('title') ?? $url }}"
                        class="object-cover object-center w-full h-56"
                    />
                <div
                    class="absolute right-0 bottom-0 left-0 w-full rounded-b-lg border-0 bg-pink-100 bg-opacity-75 p-2 backdrop-blur-sm backdrop-filter dark:bg-opacity-45 dark:bg-pink-800">
                    <h3 class="text-sm font-semibold truncate text-slate-500/90 dark:text-white/90
                    ">
                        {{ $data->get('title') ?? $data->get('site_name') ?? $url }}</h3>
                </div>
            </div>
        </a>
        <div class="flex items-center justify-between pt-4">
            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
               class="text-xs text-slate-500 group-hover/preview:text-pink-600">From: {{ $shortUrl}}</a>
        </div>
@endif
</div>
