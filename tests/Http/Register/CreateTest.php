<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->basePayload = [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
        'cf-turnstile-response' => 'valid-captcha-code',
    ];
});
function mockTurnstileResponse(bool $status): void
{
    $fixtureFile = $status
        ? base_path('tests/Fixtures/Turnstile/success.json')
        : base_path('tests/Fixtures/Turnstile/error.json');

    $jsonFixture = File::json($fixtureFile);

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response($jsonFixture),
    ]);
}

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertOk()
        ->assertSee('Register');
});

test('new users can register', function () {
    mockTurnstileResponse(true);

    $response = $this->from('/register')->post('/register', $this->basePayload);

    $this->assertAuthenticated();

    $response->assertRedirect(route('profile.show', [
        'username' => User::first()->username,
    ], absolute: false));
});

test('can not register with an invalid turnstile response', function () {

    mockTurnstileResponse(false);
    $response = $this->from('/register')->post('/register', $this->basePayload);

    $response->assertSessionHasErrors('cf-turnstile-response');
});

test('required fields', function (string $field) {
    $payload = [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $payload[$field] = '';

    $response = $this->from('/register')->post('/register', $payload);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors([
            $field => 'The '.$field.' field is required.',
        ]);
})->with(['name', 'username', 'email', 'password', 'terms']);

test('email must be valid', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'invalid-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors(['email' => 'The email field must be a valid email address.']);
});

test('email provider must be authorized', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Tomás López',
        'username' => 'tomloprod',
        'email' => 'tomloprod@0-mail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors(['email' => 'The email belongs to an unauthorized email provider.']);
});

test('password must be confirmed', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'not-password',
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors(['password' => 'The password field confirmation does not match.']);
});

test('users must be at least 18 years old', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'not-password',
        'terms' => false,
    ]);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors(['terms' => 'The terms field must be accepted.']);
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
    'feed',
    'home',
    'about',
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

test("user's name can contain blank characters", function (string $given, string $expected) {
    mockTurnstileResponse(true);
    $response = $this->from('/register')->post('/register', [
        ...$this->basePayload, 'name' => $given,
    ]);

    // Assert that the user was created
    $response->assertRedirect(); // or specific route if expected
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

    // Retrieve the user
    $user = User::where('email', 'test@example.com')->first();
    $this->assertNotNull($user, 'User should have been created.');

    // Assert the name matches expectations
    expect($user->name)->toBe($expected);
})->with([
    ["Test\u{200E}User", "Test\u{200E}User"],
    ["Test User \u{200E}", 'Test User'],
    ["Test \u{200E}\u{200E} User", "Test \u{200E}\u{200E} User"],
]);

test('anonymously preference is set to true by default', function () {
    mockTurnstileResponse(true);
    $this->from('/register')->post('/register', $this->basePayload);

    expect(User::first()->prefers_anonymous_questions)->toBeTrue();
});
