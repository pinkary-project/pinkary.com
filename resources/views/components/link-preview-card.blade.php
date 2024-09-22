@if($data->has('image'))
    <div class="mx-auto min-w-full">
        <div class="relative w-full overflow-hidden rounded-lg bg-white shadow-md">
            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">
                <img
                    x-init="const image = new Image().src = '{{ $data->get('image') }}';
                    window.onload = (image) => {
                        if (image.width < 300) {
                         $el.classList.remove('object-cover');
                         }
                    }
                    "

                    src="{{ $data->get('image') }}" alt="{{ $data->get('title') ?? $url }}"
                    title="Click to visit: {{ $data->get('title') ?? $url }}"
                     class="h-[228px] w-[513px] object-cover object-center"/>
            </a>

            <div class="absolute bottom-0 left-0 w-full bg-pink-900 bg-opacity-15 backdrop-filter backdrop-blur-md p-2 text-white">
                <h3 class="text-sm font-semibold">{{ $data->get('title') ?? $url }}</h3>
            </div>
        </div>
    </div>
    <!-- Footer Section -->
    <div class="flex items-center justify-between p-4">
        <span class="text-xs text-gray-500">From {{ parse_url($url)['host'] }}</span>
    </div>
@elseif ($data->has('html'))
    <div class="md:flex-shrink-0">
        {!! $data->get('html') !!}
    </div>
@endif
