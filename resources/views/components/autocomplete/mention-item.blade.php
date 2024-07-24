@php
    /** @var \App\Services\DynamicAutocomplete\Results\MentionResult $result */
@endphp
<div class="flex items-center justify-between">
    <div
        class="flex items-center gap-3"
    >
        <figure class="{{ $result->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10 flex-shrink-0 bg-slate-800">
            <img
                src="{{ $result->avatar_src }}"
                alt="{{ $result->username }}"
                class="{{ $result->is_company_verified ? 'rounded-md' : 'rounded-full' }} h-10 w-10"
            />
        </figure>
        <div class="overflow-hidden text-sm">
            <div class="flex items-center">
                <p class="truncate font-medium text-slate-50">
                    {{ $result->name }}
                </p>

                @if ($result->is_company_verified)
                    <x-icons.verified-company
                        color="pink-500"
                        class="ml-1 h-3.5 w-3.5"
                    />
                @elseif ($result->is_verified)
                    <x-icons.verified
                        color="pink-500"
                        class="ml-1 h-3.5 w-3.5"
                    />
                @endif
            </div>

            <p class="truncate text-slate-500">
                {{ $result->username }}
            </p>
            @if($result->is_followed_by_user)
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
