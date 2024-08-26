<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\Home\Feed;
use App\Livewire\Questions\Create;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('can see the "feed" view', function () {
    $response = $this->get(route('home.feed'));

    $response->assertOk()
        ->assertSee('Feed')
        ->assertSeeLivewire(Feed::class);
});

it('can see the question create component when logged in', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('home.feed'));

    $response->assertOk()
        ->assertSeeLivewire(Create::class);
});

it('does increment views', function () {
    Queue::fake(IncrementViews::class);

    $this->get(route('home.feed'));

    Queue::assertPushed(IncrementViews::class);
});

it('can filter questions to those with a particular hashtag', function () {
    $questionWithHashtag = Question::factory()->create(['answer' => 'question 1 with a #hashtag']);

    Question::factory()->create(['answer' => 'question 2 without hashtags']);

    $component = Livewire::test(Feed::class, ['hashtag' => 'hashtag']);

    $component
        ->assertViewHas('questions', fn (Illuminate\Pagination\Paginator $paginator): bool => $paginator
            ->pluck('id')
            ->all() === [$questionWithHashtag->id]
        )
        ->assertSee('question 1')
        ->assertDontSee('question 2')
        ->assertDontSee('There are no questions to show.');
});
