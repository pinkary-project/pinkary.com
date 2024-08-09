<div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
    <div class="md:flex">
        @if(isset($ogData['image']))
            <div class="md:flex-shrink-0">
                <img class="h-48 w-full object-cover md:w-48" src="{{ $ogData['image'] }}" alt="OpenGraph Image">
            </div>
        @endif
        <div class="p-8">
            <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold">{{ $ogData['site_name'] ?? 'Link Preview' }}</div>
            <a href="{{ $url }}" class="block mt-1 text-lg leading-tight font-medium text-black hover:underline">{{ $ogData['title'] ?? $url }}</a>
            <p class="mt-2 text-gray-500">{{ $ogData['description'] ?? '' }}</p>
        </div>
    </div>
</div>
