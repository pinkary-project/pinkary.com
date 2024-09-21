<?php

declare(strict_types=1);

use App\Livewire\Home\Recent;
use App\Livewire\Questions\Create;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

it('can see the "feed" view', function () {
    $response = $this->get(route('home.recent'));

    $response->assertOk()
        ->assertSee('Recent')
        ->assertSeeLivewire(Recent::class);
});

it('can see the question create component when logged in', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('home.recent'));

    $response->assertOk()
        ->assertSeeLivewire(Create::class);
});

it('can filter questions to those with a particular hashtag', function () {
    $questionWithHashtag = Question::factory()->create(['answer' => 'question 1 with a #hashtag']);

    Question::factory()->create(['answer' => 'question 2 without hashtags']);

    $component = Livewire::test(Recent::class, ['hashtag' => 'hashtag']);

    $component
        ->assertViewHas('questions', fn (Illuminate\Pagination\Paginator $paginator): bool => $paginator
            ->pluck('id')
            ->all() === [$questionWithHashtag->id]
        )
        ->assertSee('question 1')
        ->assertDontSee('question 2')
        ->assertDontSee('There are no questions to show.');
});
