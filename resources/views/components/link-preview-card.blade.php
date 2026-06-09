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
    @php($shortUrl = (parse_url($url)['host'] ?? $url))
    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">
        <div
            title="Click to visit: {{ $shortUrl }}"
             class="relative w-full rounded-lg overflow-hidden">
                        <img
                            src="{{ $data->get('image') }}"
                            alt="{{ $data->get('title') ?? $url }}"
                        />
                <div
                    class="absolute right-0 bottom-0 left-0 w-full bg-pink-800/55 px-2 py-1">
                    <h3 class="text-xs font-semibold truncate text-white">
                        {{ $data->get('title') ?? $data->get('site_name') ?? $url }}</h3>
                </div>
            </div>
        </a>
@endif
</div>
