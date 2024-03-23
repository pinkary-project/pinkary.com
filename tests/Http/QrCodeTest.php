<?php

declare(strict_types=1);

use App\Models\User;

test('Qr Code can be downloaded only by authenticated users', function () {
    $response = $this->get(route('qr-code.download'));

    $response->assertRedirect(route('login'));
});

test('user can download qr code', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('qr-code.download'));

    $response->assertOk()
        ->assertHeader('content-type', 'image/png')
        ->assertDownload('qr-code.png');
});
