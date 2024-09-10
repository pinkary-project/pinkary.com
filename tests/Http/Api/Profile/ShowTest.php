<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guest', function () {
    getJson(
        uri: route('api.profile.show'),
    )->assertStatus(401);
})->group('api');

test('auth', function (): void {
    actingAs($this->user)->getJson(
        uri: route('api.profile.show'),
    )->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json
        ->has('data'),
    );
})->group('api');

test('can view profile data', function (): void {
    actingAs($this->user)->getJson(
        uri: route('api.profile.show'),
    )->assertJson(fn (AssertableJson $json) => $json
        ->where('data.id', $this->user->id)
        ->where('data.name', $this->user->name)
        ->where('data.username', $this->user->username)
        ->where('data.bio', $this->user->bio)
        ->where('data.email', $this->user->email)
        ->where('data.avatar', $this->user->avatar)
        ->has('data.verification')
        ->where('data.verification.profile', $this->user->is_verified)
        ->where('data.verification.email', $this->user->hasVerifiedEmail())
        ->where('data.verification.company', $this->user->is_company_verified)
        ->etc(),
    );
})->group('api');
