<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('it can enable infinite scroll', function () {
    $response = get(route('infinite-scroll.enable'));

    $response->assertRedirect(route('home.feed'));
    $response->assertCookie('infinite-scroll', '1');
});

it('it can disable infinite scroll', function () {
    $response = get(route('infinite-scroll.disable'));

    $response->assertRedirect(route('home.feed'));
    $response->assertCookie('infinite-scroll', '0');
});
