<?php

declare(strict_types=1);

it('renders the changelog', function () {
    $this->get('/changelog')
        ->assertOk()
        ->assertViewIs('changelog');
});
