<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

test('a user can register through the API', function (): void {
    $response = postJson(route('api.v1.auth.register'), [
        'name' => 'Pinkary User',
        'username' => 'pinkaryuser',
        'email' => 'pinkary@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data', 'token'])
        ->assertJsonPath('data.username', 'pinkaryuser');

    expect(User::query()->where('email', 'pinkary@example.com')->exists())->toBeTrue();
});

test('a user can log in through the API', function (): void {
    $user = User::factory()->create([
        'email' => 'pinkary@example.com',
        'password' => Hash::make('password'),
    ]);

    postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonStructure(['data', 'token'])
        ->assertJsonPath('data.id', $user->id);
});

test('invalid credentials are rejected', function (): void {
    postJson(route('api.v1.auth.login'), [
        'email' => 'pinkary@example.com',
        'password' => 'wrong-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('a user can revoke the current API token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $headers = ['Authorization' => 'Bearer '.$token];

    postJson(route('api.v1.auth.logout'), [], $headers)->assertNoContent();

    auth()->forgetGuards();

    getJson(route('api.v1.profile.show'), $headers)->assertUnauthorized();
});

test('registration validates input and rejects duplicate email', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    postJson(route('api.v1.auth.register'), [
        'name' => 'Pinkary User',
        'username' => 'pinkaryuser',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('api routes render json exceptions even without accept header', function (): void {
    $this->get('/api/v1/questions/non-existent-uuid')
        ->assertNotFound()
        ->assertHeader('content-type', 'application/json');
});
