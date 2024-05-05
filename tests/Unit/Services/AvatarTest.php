<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Avatar;

test('avatar default url', function () {
    $avatar = new Avatar(
        user: User::factory()->create(),
    );

    expect($avatar->url())->toBe(asset('img/default-avatar.png'));
});

test('avatar url with gravatar', function () {
    $user = User::factory()->create(['email' => 'enunomaduro@gmail.com']);
    $gravHash = hash('sha256', mb_strtolower($user->email));

    $avatar = new Avatar(
        user: $user,
    );

    expect($avatar->url())->toBe("https://gravatar.com/avatar/{$gravHash}?s=300&d=404");
});

test('avatar url with github', function () {
    $user = User::factory()->create(['github_username' => 'nunomaduro']);

    $avatar = new Avatar(
        user: $user,
    );

    expect($avatar->url('github'))->toBe('https://avatars.githubusercontent.com/nunomaduro');
});
