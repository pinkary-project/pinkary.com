<?php

declare(strict_types=1);

it('redirects to telegram group', function () {
    $response = $this->get(route('telegram'));

    $response->assertRedirect('https://t.me/+Yv9CUTC1q29lNzg8');
});
