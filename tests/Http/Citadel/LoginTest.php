<?php

declare(strict_types=1);

test('login route is not accessible', function (): void {
    $response = $this->get('citadel/login');

    $response->assertStatus(404);
});
