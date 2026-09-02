<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.components.head')
</head>
@php
    $showDiscoverLayout = request()->routeIs('home.*') || request()->routeIs('hashtag.show') || request()->routeIs('channels.*');
    $showUtilityRail = request()->routeIs('bookmarks.*') || request()->routeIs('notifications.*');
    $showRightRail = $showDiscoverLayout || $showUtilityRail || request()->routeIs('profile.show') || request()->routeIs('questions.show');
    $globalSearchQuery = request()->routeIs('home.users')
        ? (string) request()->query('q', '')
        : (request()->routeIs('hashtag.show') ? '#'.request()->route('hashtag') : '');
@endphp
<body class="bg-slate-100 font-sans text-slate-950 antialiased dark:bg-[#060c18] dark:text-slate-50">
    @persist('flash-messages')
        <livewire:flash-messages.show />
    @endpersist
    <div class="pointer-events-none fixed inset-0 -z-10 hidden overflow-hidden dark:block">
        <div class="absolute inset-0 bg-[linear-gradient(180deg,#040915_0%,#081223_100%)]"></div>
        <div class="absolute inset-x-0 top-0 h-48 bg-[radial-gradient(circle_at_top,rgba(236,72,153,0.12),transparent_60%)]"></div>
        <div class="absolute top-0 left-[28%] h-64 w-64 -translate-x-1/2 rounded-full bg-pink-500/8 blur-3xl"></div>
        <div class="absolute top-24 -right-20 h-72 w-72 rounded-full bg-sky-500/6 blur-3xl"></div>
    </div>

    <div class="relative flex min-h-screen flex-col">
        <div class="mx-auto flex w-full max-w-7xl flex-1 px-0 pb-28 lg:grid lg:pb-0 {{ $showRightRail ? 'lg:grid-cols-[18rem_minmax(0,1fr)_22.5rem]' : 'lg:grid-cols-[18rem_minmax(0,1fr)]' }}">
            <aside class="lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col">
                @include('layouts.navigation')
            </aside>

            <div class="min-w-0 flex-1 {{ $showDiscoverLayout ? 'lg:col-start-2' : '' }} {{ $showRightRail ? 'lg:pr-2' : '' }}">
                @if ($showDiscoverLayout)
                    <form
                        x-data="{ query: @js($globalSearchQuery) }"
                        x-on:submit.prevent="
                                const value = query.trim();

                                if (! value) {
                                    return;
                                }

                                if (value.startsWith('#')) {
                                    window.location.assign('{{ url('/hashtag') }}/' + encodeURIComponent(value.replace(/^#/, '')));
                                    return;
                                }

                                const params = new URLSearchParams({ q: value });

                                window.location.assign('{{ route('home.users') }}?' + params.toString());
                            "
                        class="hidden items-center gap-3 border-r border-b border-slate-200/70 bg-white/90 px-6 py-4 lg:flex dark:border-slate-800/30 dark:bg-[#050c1d]/90"
                    >
                        <x-heroicon-o-magnifying-glass class="size-5 text-slate-400 dark:text-slate-500" />

                        <input
                            x-model="query"
                            type="text"
                            name="q"
                            autocomplete="off"
                            placeholder="Search for users or hashtags..."
                            class="w-full border-0 bg-transparent p-0 text-sm text-slate-950 placeholder:text-slate-400 focus:ring-0 focus:outline-none dark:text-white dark:placeholder:text-slate-500"
                        />
                    </form>

                    <form
                        x-data="{ query: @js($globalSearchQuery) }"
                        x-on:submit.prevent="
                                const value = query.trim();

                                if (! value) {
                                    return;
                                }

                                if (value.startsWith('#')) {
                                    window.location.assign('{{ url('/hashtag') }}/' + encodeURIComponent(value.replace(/^#/, '')));
                                    return;
                                }

                                const params = new URLSearchParams({ q: value });

                                window.location.assign('{{ route('home.users') }}?' + params.toString());
                            "
                        class="flex h-[57px] items-center gap-3 border-b border-slate-200/70 bg-white px-6 sm:bg-white/95 sm:backdrop-blur lg:hidden dark:border-slate-800/30 dark:bg-[#050c1d] dark:sm:bg-[#050c1d]/95"
                    >
                        <x-heroicon-o-magnifying-glass class="size-5 text-slate-400 dark:text-slate-500" />

                        <input
                            x-model="query"
                            type="text"
                            name="q"
                            autocomplete="off"
                            placeholder="Search users or hashtags..."
                            class="w-full border-0 bg-transparent p-0 text-sm text-slate-950 placeholder:text-slate-400 focus:ring-0 focus:outline-none dark:text-white dark:placeholder:text-slate-500"
                        />
                    </form>
                @endif

                @if (isset($title))
                    <div class="{{ $showDiscoverLayout ? 'mb-6 w-full pt-2 lg:pt-0' : 'mx-auto mb-6 w-full max-w-176 px-4 pt-2 lg:px-4 lg:pt-4' }}">
                        <h1 class="font-mona text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                            {{ $title }}
                        </h1>
                    </div>
                @endif

                <main class="w-full">{{ $slot }}</main>

                <x-image-lightbox />
            </div>

            @if ($showRightRail)
                <aside class="hidden lg:col-start-3 lg:block lg:pl-2 {{ $showDiscoverLayout ? 'lg:pt-[57px]' : 'lg:pt-4' }}">
                    <div class="lg:sticky lg:top-4">
                        <livewire:people-to-follow
                            :context="$peopleToFollowContext"
                            :contextUserId="$peopleToFollowUserId"
                            :contextQuestionId="$peopleToFollowQuestionId"
                        />
                    </div>
                </aside>
            @endif
        </div>

        @persist('footer')
            <x-back-to-top :offset="300" />
            <x-footer />
        @endpersist
    </div>

    @auth
        <button
            x-data
            type="button"
            x-on:click="$dispatch('open-modal', 'post-create')"
            class="fixed right-5 bottom-20 z-50 flex size-12 items-center justify-center rounded-full bg-pink-500 text-white shadow-lg shadow-pink-500/30 transition hover:scale-105 hover:bg-pink-600 focus:outline-none active:scale-95 lg:hidden"
            title="Post"
            aria-label="Post"
        >
            <x-icons.compose class="size-5" />
        </button>

        <x-modal
            max-width="2xl"
            name="post-create"
            focusable
            focus-target="last-thread-post"
            x-on:question.created.window="
                window.__postJustPublished = true;
                close('post-create');
            "
        >
            <div
                class="flex max-h-[calc(100dvh-3rem)] flex-col"
                x-init="
                    $watch('show', (value) => {
                        if (value) {
                            const channelMeta = document.querySelector('[data-current-channel-id]');
                            if (channelMeta) {
                                const channelId = parseInt(channelMeta.getAttribute('data-current-channel-id'), 10);
                                const channelName = channelMeta.getAttribute('data-current-channel-name');
                                if (channelId && channelName) {
                                    $nextTick(() => {
                                        window.dispatchEvent(
                                            new CustomEvent('channel-selected', {
                                                detail: { id: channelId, name: channelName },
                                            }),
                                        );
                                    });
                                }
                            } else {
                                const composer = $el.querySelector('[data-post-composer]');
                                const state = composer
                                    ? window.Alpine?.$data
                                        ? window.Alpine.$data(composer)
                                        : composer._x_dataStack?.[0]
                                    : null;
                                const picker = composer ? composer.querySelector('[data-channel-picker]') : null;
                                const pickerHasChannel = picker && picker.hasAttribute('data-selected-id');
                                if (state && ! state.hasDraft() && ! pickerHasChannel) {
                                    $nextTick(() => {
                                        window.dispatchEvent(
                                            new CustomEvent('channel-selected', {
                                                detail: null,
                                            }),
                                        );
                                    });
                                }
                            }

                            if (window.__postJustPublished) {
                                window.__postJustPublished = false;
                            }

                            return;
                        }

                        const composer = $el.querySelector('[data-post-composer]');

                        if (! composer) {
                            return;
                        }

                        const state = Alpine.$data(composer);

                        if (! state.hasDraft()) {
                            return;
                        }

                        $dispatch('open-modal', 'discard-post-draft');
                    })
                "
            >
                <div class="border-b border-slate-200/70 px-4 pt-4 pb-3 sm:px-6 sm:pt-6 dark:border-slate-800/40">
                    <h3 class="text-base font-semibold text-slate-950 dark:text-white">{{ __('Share an update') }}</h3>
                </div>

                <div class="flex min-h-0 flex-1 flex-col px-4 pt-4 pb-4 sm:px-6 sm:pb-6">
                    <livewire:questions.create
                        :to-id="auth()->id()"
                        :custom-draft-key="'post_modal'"
                        key="global-modal-create-post"
                    />
                </div>
            </div>
        </x-modal>

        <x-modal name="discard-post-draft" max-width="md" class="z-110">
            <div class="p-8">
                <h2 class="text-lg font-medium text-slate-950 dark:text-slate-50">{{ __('Discard this post?') }}</h2>
                <div class="mt-4 text-slate-500 dark:text-slate-400">
                    <p>{{ __('Are you sure you want to discard this post? Your progress will be lost.') }}</p>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <x-secondary-button
                        x-on:click="
                            $dispatch('close-modal', 'discard-post-draft');
                            $dispatch('open-modal', 'post-create');
                        "
                    >
                        {{ __('Keep editing') }}
                    </x-secondary-button>
                    <x-primary-button
                        x-on:click="
                            const composer = document.querySelector('[data-post-composer][data-draft-key=post_modal]');
                            if (composer) {
                                Alpine.$data(composer).discardDraft();
                            }
                            $dispatch('close-modal', 'discard-post-draft');
                        "
                    >
                        {{ __('Discard') }}
                    </x-primary-button>
                </div>
            </div>
        </x-modal>
    @endauth

    @livewireScriptConfig

    <script>
        window.onload = function () {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            if (timezone !== '{{ session()->get('timezone', 'UTC') }}') {
                axios.post('{{ route('profile.timezone.update') }}', { timezone });
            }

            Livewire.interceptRequest(({ request, onResponse, onSuccess, onError, onFailure }) => {
                onError(({ response, responseBody, preventDefault }) => {
                    if (response && response.status === 419) {
                        preventDefault();
                    }
                });
            });
        };
    </script>
</body>
</html>
