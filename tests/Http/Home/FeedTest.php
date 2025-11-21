<?php

declare(strict_types=1);

use App\Livewire\Home\Feed;
use App\Livewire\Questions\Create;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\get;
use function Pest\Laravel\withCookie;

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

it('can show pagination buttons if infinite scroll is disabled', function () {
    Question::factory(20)->create();

    $response = withCookie('infinite-scroll', '0')
        ->get(route('home.feed'));

    $response->assertOk()
        ->assertSeeText('Previous')
        ->assertSeeText('Next');
});

it('can hide pagination buttons if infinite scroll is enabled', function () {
    Question::factory(20)->create();

    $response = withCookie('infinite-scroll', '1')
        ->get(route('home.feed'));

    $response->assertOk()
        ->assertDontSeeText('Previous')
        ->assertDontSeeText('Next');
});

test('infinite scroll is the default mode', function () {
    $response = get(route('home.feed'));

    $response->assertOk()
        ->assertDontSeeText('Previous')
        ->assertDontSeeText('Next');
});
