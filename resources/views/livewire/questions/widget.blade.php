<section class="mt-4 space-y-10">
    @foreach ($this->questions as $question)
        <x-questions.show :question="$question" :pinnable="$pinnable" />
    @endforeach

    <x-load-more-button :perPage="$perPage" :paginator="$this->questions"
        message="There are no more questions to load, or you have scrolled too far." />
</section>
