<?php

declare(strict_types=1);

use App\Services\Changelog;

it('renders the changelog', function (): void {
    $releases = (new Changelog)->getReleases();
    $latestVersion = array_key_first($releases);

    $this->get('/changelog')
        ->assertOk()
        ->assertViewIs('changelog')
        ->assertSee('Changelog')
        ->assertSee('Version '.$latestVersion);
});
