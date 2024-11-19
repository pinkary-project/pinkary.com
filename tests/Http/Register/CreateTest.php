<?php

declare(strict_types=1);

use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

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
        'terms' => true,
        'g-recaptcha-response' => 'valid',
    ]);

    $this->assertAuthenticated();

    $user = User::first();

    $response->assertRedirect(route('profile.show', [
        'username' => $user->username,
    ], absolute: false));

    expect($user->email_verified_at)->toBeNull();
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
    $response = $this->from('/register')->post('/register', [
        'name' => $given,
        'username' => 'testuser',
        'email' => 'test@laravel.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ]);

    $user = User::where('email', 'test@laravel.com')->first();

    expect($user->name)->toBe($expected);
})->with([
    ["Test\u{200E}User", "Test\u{200E}User"],
    ["Test User \u{200E}", 'Test User'],
    ["Test \u{200E}\u{200E} User", "Test \u{200E}\u{200E} User"],
]);

test('anonymously preference is set to true by default', function () {
    $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser1',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ]);

    expect(User::first()->prefers_anonymous_questions)->toBeTrue();
});

test('users coming from GitHub login see the data retrieved from GitHub', function () {
    $githubEmail = 'test@example.com';
    $githubUsername = 'test_username';

    session([
        'github_email' => $githubEmail,
        'github_username' => $githubUsername,
    ]);

    $response = $this->get('/register');

    expect($response->content())->toContain($githubEmail);
    expect($response->content())->toContain($githubUsername);
});

test('users coming from GitHub login can register and connect to GitHub', function () {
    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
        ]),
    ]);

    $githubEmail = 'test@example.com';
    $githubUsername = 'test';

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => $githubEmail,
        'password' => 'm@9v_.*.XCN',
        'password_confirmation' => 'm@9v_.*.XCN',
        'terms' => true,
        'g-recaptcha-response' => 'valid',
        'github_username' => $githubUsername,
    ]);

    $this->assertAuthenticated();

    $user = User::first();

    $response->assertRedirect(route('profile.show', [
        'username' => $user->username,
    ], absolute: false));

    expect(session('flash-message'))->toBe('Your GitHub account has been connected.');

    expect($user->github_username)->toBe('test');
    expect($user->is_verified)->toBeFalse();
    expect($user->email_verified_at)->not()->toBeNull();
});

test('users coming from GitHub login when existing GitHub username', function () {
    User::factory()->create([
        'github_username' => 'test',
    ]);

    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
        ]),
    ]);

    $githubEmail = 'test@example.com';
    $githubUsername = 'test';

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => $githubEmail,
        'password' => 'm@9v_.*.XCN',
        'password_confirmation' => 'm@9v_.*.XCN',
        'terms' => true,
        'g-recaptcha-response' => 'valid',
        'github_username' => $githubUsername,
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('register'));

    $this->assertGuest();

    $response->assertSessionHasErrors('github_username', 'This GitHub username is already connected to another account.', 'github');

    expect(session('github_email'))->toBeNull();
    expect(session('github_username'))->toBeNull();
});

test('users coming from GitHub login can register and get verified if they are sponsoring us', function () {
    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
        ]),
    ]);

    Http::fake([
        'github.com/*' => Http::response([
            'data' => [
                'user' => [
                    'sponsorshipForViewerAsSponsorable' => [
                        [
                            'monthlyPriceInDollars' => 9,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $githubEmail = 'test@example.com';
    $githubUsername = 'test';

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => $githubEmail,
        'password' => 'm@9v_.*.XCN',
        'password_confirmation' => 'm@9v_.*.XCN',
        'terms' => true,
        'g-recaptcha-response' => 'valid',
        'github_username' => $githubUsername,
    ]);

    $this->assertAuthenticated();

    $user = User::first();

    $response->assertRedirect(route('profile.show', [
        'username' => $user->username,
    ], absolute: false));

    expect(session('flash-message'))->toBe('Your GitHub account has been connected and you are now verified.');

    expect($user->github_username)->toBe('test');
    expect($user->is_verified)->toBeTrue();
});

test('users coming from GitHub login fetch github avatar', function () {
    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
        ]),
    ]);
    Queue::fake();

    $githubEmail = 'test@example.com';
    $githubUsername = 'test';

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => $githubEmail,
        'password' => 'm@9v_.*.XCN',
        'password_confirmation' => 'm@9v_.*.XCN',
        'terms' => true,
        'g-recaptcha-response' => 'valid',
        'github_username' => $githubUsername,
    ]);

    $this->assertAuthenticated();

    $user = User::first();

    $response->assertRedirect(route('profile.show', [
        'username' => $user->username,
    ], absolute: false));

    Queue::assertPushed(UpdateUserAvatar::class);

    $job = new UpdateUserAvatar(
        user: $user,
        service: 'github',
    );

    $job->handle();

    expect($job)->toBeInstanceOf(UpdateUserAvatar::class);

    expect($user->github_username)->toBe('test')
        ->and($user->is_verified)->toBeFalse()
        ->and($user->avatar)->toContain('avatars/')
        ->and($user->avatar)->toContain('.png')
        ->and($user->avatar_updated_at)->not()->toBeNull()
        ->and($user->is_uploaded_avatar)->toBeFalse();
});
