<?php

declare(strict_types=1);

test('redirect to github', function () {
    $response = $this->get(route('profile.connect.github.login'));

    $response->assertStatus(302);
    $response->assertRedirectContains('https://github.com/login/oauth/authorize');
    $response->assertRedirectContains('callback');
});
