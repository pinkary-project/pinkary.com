<?php

declare(strict_types=1);

test('/for-you redirects to /following', function () {
    $response = $this->get('/for-you');

    $response->assertRedirect('/following');
});
