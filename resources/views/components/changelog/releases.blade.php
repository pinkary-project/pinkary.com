@foreach($releases as $version => $release)
    <li class="relative flex">
        <div class="relative size-6 flex-none items-center justify-center mr-4 mt-3.5 hidden sm:flex">
            <div class="size-1.5 rounded-full bg-pink-500 ring-1 ring-pink-500"></div>
        </div>

        <div class="relative rounded-xl flex-1 overflow-hidden border border-slate-900">
            <header class="flex-1 border-b border-slate-900 w-full px-4 py-3.5 flex items-center text-slate-200 justify-between">
                <h2 class="font-bold">Version {{ $version }}</h2>
                <time datetime="{{ $release['published_at'] }}" class="flex-none py-0.5 text-xs leading-5 text-slate-500 font-semibold">{{ $release['published_at'] }}</time>
            </header>
            <div class="prose prose-invert prose-sm prose-h3:text-sm px-4 py-3.5">
                @if($release['changes'])
                    <h3>Improvements & Bug fixes</h3>
                    <ul>
                        @foreach($release['changes'] as $change)
                            <li>{{ $change }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="absolute -top-[200px] -right-[200px] transform-gpu blur-3xl z-10 opacity-20">
                <div class="size-[600px] bg-gradient-to-r from-pink-900 to-pink-500" style="clip-path:polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)"></div>
            </div>

            <div class="absolute inset-x-0 -bottom-2 flex h-2 justify-center overflow-hidden">
                <div class="-mt-px flex h-[2px] w-2/3 absolute right-5">
                    <div class="w-full flex-none blur-sm bg-gradient-to-r from-slate-950 via-pink-400 to-slate-950"></div>
                </div>
            </div>
        </div>
    </li>
@endforeach
