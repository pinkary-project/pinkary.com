<?php

declare(strict_types=1);

use App\Models\User;

it('shows recent posts with the route-binded hashtag', function () {
    $user = User::factory()->create();
    App\Models\Question::factory()->create(['answer' => '#hashtag1']);
    App\Models\Question::factory()->create(['answer' => 'not a hashtag in sight']);

    $response = $this->actingAs($user)->get('/hashtag/hashtag1');

    $response
        ->assertOk()
        ->assertSee('#hashtag1')
        ->assertDontSee('not a hashtag in sight');
});

it('guests are allowed to view', function () {
    App\Models\Question::factory()->create(['answer' => '#hashtag1']);

    $response = $this->get('/hashtag/hashtag1');

    $response
        ->assertOk()
        ->assertSee('#hashtag1');
});
