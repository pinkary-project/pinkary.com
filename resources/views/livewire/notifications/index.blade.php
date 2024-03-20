<div>
    @foreach ($user->notifications as $notification)
        @php
            $question = \App\Models\Question::find($notification->data['question_id']);
        @endphp

        <div class="hover:bg-gray-800">
            <a
                href="{{ route('questions.show', ['user' => $question->to->username, 'question' => $question->id]) }}"
                wire:navigate
            >
                <div class="rounded-lg px-2 py-4">
                    @if ($question->anonymously)
                        <div class="items center flex">
                            <div>
                                <p class="pt-3 text-gray-400">Anonymously</p>
                            </div>
                        </div>
                    @else
                        <div class="items center flex">
                            <img
                                src="{{ $question->from->avatar ? url($question->from->avatar) : $question->from->avatar_url }}"
                                alt="{{ $question->from->username }}"
                                class="h-12 w-12 rounded-full"
                            />
                            <div class="ml-2">
                                <div class="items flex">
                                    <p class="text-gray-400">
                                        {{ $question->from->name }}
                                    </p>
                                    @if ($question->from->is_verified)
                                        <svg aria-label="Verified" class="text-{{ $question->from->right_color }} ml-1 mt-1 fill-current saturate-200" height="15" role="img" viewBox="0 0 40 40" width="18"><title>Verified</title><path d="M19.998 3.094 14.638 0l-2.972 5.15H5.432v6.354L0 14.64 3.094 20 0 25.359l5.432 3.137v5.905h5.975L14.638 40l5.36-3.094L25.358 40l3.232-5.6h6.162v-6.01L40 25.359 36.905 20 40 14.641l-5.248-3.03v-6.46h-6.419L25.358 0l-5.36 3.094Zm7.415 11.225 2.254 2.287-11.43 11.5-6.835-6.93 2.244-2.258 4.587 4.581 9.18-9.18Z" fill-rule="evenodd" ></path></svg>
                                    @endif
                                </div>

                                <div class="text-gray-600 hover:underline">
                                    {{ '@'.$question->from->username }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <p class="mt-2 text-gray-200">
                        {!! $question->content !!}
                    </p>
                </div>
            </a>
        </div>

        @if (! $loop->last)
            <div class="border-t border-gray-800"></div>
        @endif
    @endforeach

    @if ($user->notifications->count() === 0)
        <div class="rounded-lg">
            <p class="text-gray-400">No pending questions.</p>
        </div>
    @endif
</div>
