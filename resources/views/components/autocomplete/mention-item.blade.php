@php
    /** @var \App\Services\Autocomplete\Result $result */
@endphp
<div class="flex items-center justify-between">
    <div
        class="flex items-center gap-3"
    >
        <figure
            class="{{ $result->payload['isCompanyVerified'] ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 dark:bg-slate-800 bg-slate-50">
            <img
                src="{{ $result->payload['avatarSrc'] }}"
                alt="{{ $result->payload['username'] }}"
                class="{{ $result->payload['isCompanyVerified'] ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
            />
        </figure>
        <div class="overflow-hidden text-sm">
            <div class="flex items-center">
                <p class="truncate font-medium dark:text-white text-black">
                    {{ $result->payload['name'] }}
                </p>

                @if ($result->payload['isCompanyVerified'])
                    <x-icons.verified-company
                        color="pink-500"
                        class="ml-1 h-3.5 w-3.5"
                    />
                @elseif ($result->payload['isVerified'])
                    <x-icons.verified
                        color="pink-500"
                        class="ml-1 h-3.5 w-3.5"
                    />
                @endif
            </div>

            <p class="truncate text-slate-500">
                {{ $result->payload['username'] }}
            </p>
            @if($result->payload['isFollowedByUser'])
                <div class="truncate text-slate-500 flex items-center">
                    <x-icons.user
                        class="mr-1 h-3 w-3"
                        stroke-width="2.5"
                    />
                    Following
                </div>
            @endif
        </div>
    </div>
</div>
