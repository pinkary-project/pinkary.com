<?php

declare(strict_types=1);

use App\Models\User;

it('logs out and redirect to the root path', function () {
    $user = User::factory()->create();

    $this->be($user);

    // First, navigate to a page that requires authentication to test the redirect after logout.
    $this->get(route('profile.edit'))->assertOk();
    $this->post(route('logout'))->assertRedirect(route('welcome'));

    $this->assertGuest();
});

test('users can only logout when authenticated', function () {
    $this->assertGuest();

    $response = $this->post('/logout');

    $this->assertGuest();

    $response->assertRedirect('/login');
});
