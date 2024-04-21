<div class="mb-12 w-full text-slate-200">
    @auth
        @if ($forYouQuestions->isEmpty())
            <section class="rounded-lg">
                <p class="my-8 text-center text-lg text-slate-500">We haven't found any questions that may interest you based on the activity you've done on Pinkary.</p>
            </section>
        @else
            <section class="mb-12 min-h-screen space-y-10">
                @foreach ($forYouQuestions as $question)
                    <livewire:questions.show :questionId="$question->id" :key="'question-' . $question->id" :inIndex="true" :pinnable="false" />
                @endforeach

                <x-load-more-button
                    :perPage="$perPage"
                    :paginator="$forYouQuestions"
                    message="There are no more questions to load, or you have scrolled too far."
                />
            </section>
        @endif
    @else
        <div class="mb-4">
            <div class="mb-4">Log in or sign up to access personalized content.</div>

            <a href="{{ route('login') }}" wire:navigate>
                <x-primary-button>Log In</x-primary-button>
            </a>
            <a href="{{ route('register') }}" wire:navigate>
                <x-primary-button>Register</x-primary-button>
            </a>
        <div>
    @endauth
</div>
