<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\getJson;

test('a guest cannot view the API profile', function (): void {
    getJson(route('api.v1.profile.show'))->assertUnauthorized();
});

test('an authenticated user can view their API profile', function (): void {
    $user = User::factory()->create([
        'bio' => 'Building the social layer of the internet.',
    ]);
    $token = $user->createToken('test')->plainTextToken;

    getJson(route('api.v1.profile.show'), [
        'Authorization' => 'Bearer '.$token,
    ])->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', $user->name)
        ->assertJsonPath('data.username', $user->username)
        ->assertJsonPath('data.bio', $user->bio)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonStructure([
            'data' => [
                'verification' => ['profile', 'email', 'company'],
                'member_since' => ['human', 'string'],
            ],
        ]);
});
