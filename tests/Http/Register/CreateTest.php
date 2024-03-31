<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertOk()
        ->assertSee('Register');
});

test('new users can register', function () {
    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
        ]),
    ]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'm@9v_.*.XCN',
        'password_confirmation' => 'm@9v_.*.XCN',
        'timezone' => 'UTC',
        'g-recaptcha-response' => 'valid',
    ]);

    $this->assertAuthenticated();

    $response->assertRedirect(route('profile.show', [
        'username' => User::first()->username,
    ], absolute: false));
});

test('required fields', function (string $field) {
    $payload = [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'UTC',
    ];

    $payload[$field] = '';

    $response = $this->from('/register')->post('/register', $payload);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors([
            $field => 'The '.$field.' field is required.',
        ]);
})->with(['name', 'username', 'email', 'password']);

test('email must be valid', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'invalid-email',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'UTC',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors(['email' => 'The email field must be a valid email address.']);
});

test('password must be confirmed', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'not-password',
        'timezone' => 'UTC',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors(['password' => 'The password field confirmation does not match.']);
});

test('username must be unique', function () {
    User::factory()->create([
        'username' => 'testuser',
    ]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'UTC',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors(['username' => 'The username has already been taken.']);
});

test('email must be unique', function () {
    User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser1',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'UTC',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors([
            'email' => 'The email has already been taken.',
        ]);
});

test('password must be at least 8 characters', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'pass',
        'password_confirmation' => 'pass',
        'timezone' => 'UTC',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors([
            'password' => 'The password field must be at least 8 characters.',
        ]);
});

test('username must have 2 letters', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => '1111a',
        'email' => 'test@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'UTC',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors([
            'username' => 'The username must contain at least 2 letters.',
        ]);
});

test('username can only have letters, numbers and underscores', function (string $username) {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => $username,
        'email' => 'test@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'UTC',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors('username', 'The username may only contain letters, numbers, and underscores.');
})->with([
    'username-!',
    'username space',
    'username$',
    'username%',
    'username^',
    'username&',
    'username*',
    'username(',
    'username)',
    'username-',
    'username=',
    'username+',
    'username[',
    'username]',
    'username{',
    'username}',
    'username|',
    'username:',
    'username;',
    'username"',
    'username\'',
    'username<',
    'username>',
    'username,',
    'username.',
    'username?',
    'username/',
    'username\\',
    'username`',
    'username~',
    'username@',
    'username#',
]);

test('username is not reserved', function (string $username) {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => $username,
        'email' => 'test@laravel.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'UTC',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors([
            'username' => 'The username is reserved.',
        ]);
})->with([
    'admin',
    'superuser',
    'moderator',
    'user',
    'users',
    'settings',
    'profile',
    'account',
    'accounts',
    'dashboard',
    'home',
    'welcome',
    'login',
    'register',
    'logout',
]);

test('unique constraint validation is case insensitive', function (string $existing, $new) {
    User::factory()->create([
        'email' => '1@gmail.com',
        'username' => $existing,
    ]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => $new,
        'email' => '2@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'UTC',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors([
            'username' => 'The username has already been taken.',
        ]);
})->with([
    ['testuser', 'TESTUSER'],
    ['testuser', ' TESTUSER'],
    ['aaaaa', 'aaaaA'],
]);

test("user's name cannot contain blank characters", function (string $name) {
    $response = $this->from('/register')->post('/register', [
        'name' => $name,
        'username' => 'testuser',
        'email' => 'test@laravel.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'UTC',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors(['name' => 'The name field cannot contain blank characters.']);
})->with([
    "\u{200E}",
    "Test\u{200E}User",
    "Test User \u{200E}",
    "\u{200E}Test User",
]);

test('anonymously preference is set to true by default', function () {
    $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser1',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'UTC',
    ]);

    expect(User::first()->prefers_anonymous_questions)->toBeTrue();
});
