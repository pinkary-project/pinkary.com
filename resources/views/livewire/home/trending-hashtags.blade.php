<div>
    @foreach ($hashtags as $hashtag)
        <a class="bg-slate-600/20 p-4 shadow-2xl backdrop-blur font-medium me-2 py-1 px-2 rounded" href="{{ route('hashtag.show', $hashtag->name) }}">#{{ $hashtag->name }}</a>
    @endforeach
</div>
