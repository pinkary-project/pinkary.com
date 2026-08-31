<?php

declare(strict_types=1);

test('timezone can be updated', function () {
    $response = $this->post(route('profile.timezone.update'), [
        'timezone' => 'Pacific/Midway',
    ]);

    $response->assertNoContent();

    expect(session('timezone'))->toBe('Pacific/Midway');
});

test('timezone is required', function () {
    $response = $this->post(route('profile.timezone.update'), []);

    $response->assertSessionHasErrors('timezone');
});

test('timezone must be a string', function () {
    $response = $this->post(route('profile.timezone.update'), [
        'timezone' => ['not-a-string'],
    ]);

    $response->assertSessionHasErrors('timezone');
});

test('timezone must be a valid timezone', function () {
    $response = $this->post(route('profile.timezone.update'), [
        'timezone' => 'Not/A/Real-Timezone',
    ]);

    $response->assertSessionHasErrors('timezone');
});