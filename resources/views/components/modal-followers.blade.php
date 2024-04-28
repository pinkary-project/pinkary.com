<x-modal name="followers" maxWidth="lg">
    <div class="p-10">
        <div>
            @if($user->followers->count())
                <strong>
                    <span>@</span>{{ $user->username }} followers
                </strong>
            @else
                <strong>
                    <span>@</span>{{ $user->username }} does not have any followers
                </strong>
            @endif
        </div>

        @if($user->followers->count())
            <ul class="mt-5 space-y-2 flex flex-col items-start px-28">
                @foreach($user->followers as $follower)
                    <li>
                        <a href="{{ route('profile.show', $follower->username) }}" class="flex items-center justify-center space-x-2 hover:cursor-pointer">
                            <div class="size-10">
                                <img class="rounded-full" src="{{ $follower->avatar ? url($follower->avatar) : $follower->avatar_url }}" alt="{{ $follower->name }} avatar">
                            </div>
                            <span>{{ $follower->username }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-modal>
