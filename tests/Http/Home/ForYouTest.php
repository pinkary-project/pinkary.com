<?php

declare(strict_types=1);

use App\Livewire\Home\QuestionsForYou;
use App\Models\User;

it('can see the "following" view', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home.for_you'));

    $response->assertOk()
        ->assertSee('Following')
        ->assertSeeLivewire(QuestionsForYou::class);
});

it('guest can see the "following" view', function () {
    $response = $this->get(route('home.for_you'));

    $response->assertOk()
        ->assertSee('Log in or sign up to access personalized content');
});
