<?php

test('login route is not accessible', function () {
    $response = $this->get('citadel/login');

    $response->assertStatus(404);
});
