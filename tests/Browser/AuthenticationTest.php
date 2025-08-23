<?php

declare(strict_types=1);
use App\Models\User;

test('user can register for a new account', function (): void {
    $page = visit('/register');

    $page->assertPathContains('register')
        ->assertSee('Register');

    $page->type('name', 'John Doe')
        ->type('username', 'johndoe')
        ->type('email', 'john@example.com')
        ->type('password', 'password')
        ->type('password_confirmation', 'password')
        ->check('#terms');

    $page->script(<<<'EOT'
        const terms = document.querySelector('#terms');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'cf-turnstile-response';
        input.value = 'valid-captcha-code';
        terms.parentNode.insertBefore(input, terms.nextSibling);
    EOT);

    $page->submit();

    $page->assertPathIs('/');
})->skip();

test('user can login', function (): void {
    User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
    ]);

    $page = visit('/login');

    $page->assertPathContains('login');

    $page->type('email', 'john@example.com')
        ->type('password', 'password')
        ->wait(2);

    $page->submit();

    $page->assertPathIs('/');
});
