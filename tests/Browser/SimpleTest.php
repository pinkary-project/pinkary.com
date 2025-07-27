<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('simple external site test', function (): void {
    $page = visit('https://example.com');

    $page->assertSee('Example Domain');
});

test('user can visit the homepage', function (): void {
    $page = visit('/');

    $page->assertSee('Pinkary');
});
