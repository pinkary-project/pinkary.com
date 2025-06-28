<div>
    <h2 class="font-bold text-lg font-['poppins'] text-gray-800">What's Happening</h2>
    <div class="mt-2 grid divide-y">
        @foreach ($this->tags() as $tag)
            <a href="{{ route('hashtag.show', ['hashtag' => $tag->name]) }}"
                class="rounded-sm px-3 py-3 text-sm font-semibold hover:text-blue-500 cursor-pointer duration-39">
                <div>
                    <span class="text-base">#{{ $tag->name }}</span>
                    <div class="text-xs text-gray-700">
                        <span class="font-normal text-xs">{{ $tag->questions_count }}</span> <span class="font-medium">Posts</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
