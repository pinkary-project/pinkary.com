<x-app-layout>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full max-w-md overflow-hidden rounded-lg px-2 shadow-md sm:px-0">
            <x-home-menu></x-home-menu>

            @auth
                <livewire:questions.create :toId="auth()->id()"/>
            @endauth

            {{--
                This stuff is only here to compare the htmx options.
                It would go away for production.
            --}}
            @if(isset($questions))
                <x-questions.list :questions="$questions"></x-questions.list>
            @else
                <livewire:home.feed/>
            @endif
        </div>
    </div>
</x-app-layout>
