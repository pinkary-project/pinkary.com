<?php

declare(strict_types=1);

use App\Livewire\Home\QuestionsFollowing;
use App\Models\User;

it('/for-you redirects to /following', function () {
    $response = $this->get('/for-you');

    $response->assertRedirect('/following');
});

it('can see the "following" view', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home.following'));

    $response->assertOk()
        ->assertSee('Following')
        ->assertSeeLivewire(QuestionsFollowing::class);
});

it('guest can see the "following" view', function () {
    $response = $this->get(route('home.following'));

    $response->assertOk()
        ->assertSee('Log in or sign up to access personalized content');
});
