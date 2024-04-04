<x-modal name="following" maxWidth="lg">
    <div class="p-10">
        <div>
            @if($user->following->count())
                <strong>
                    <span>@</span>{{ $user->username }} follows
                </strong>
            @else
                <strong>
                    <span>@</span>{{ $user->username }} does not follow anyone
                </strong>
            @endif
        </div>

        @if($user->following->count())
            <ul class="mt-5">
                @foreach($user->following as $follow)
                    <li>
                        <a href="{{ route('profile.show', $follow->username) }}" class="flex items-center justify-center space-x-2 hover:cursor-pointer">
                            <div class="size-10">
                                <img class="rounded-full" src="{{ $follow->avatar ? url($follow->avatar) : $follow->avatar_url }}" alt="{{ $follow->name }} avatar">
                            </div>
                            <span>{{ $follow->username }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-modal>
