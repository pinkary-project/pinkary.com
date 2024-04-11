<?php

declare(strict_types=1);

test('redirect to user page if given url is a username', function () {
    $user = App\Models\User::factory()->create();
    $response = $this->get($user->username);
    $response->assertRedirectToRoute('profile.show', $user->username);
});

test('abort with 404 if url is not a username or an existing route', function () {
    $response = $this->get('/i-dont-exist');
    $response->assertStatus(404);
});
