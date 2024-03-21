<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureVerifiedEmailsForSignInUsers;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

test('guest', function () {
    Route::get('/test', fn (): Response => response(status: 200))
        ->middleware(EnsureVerifiedEmailsForSignInUsers::class);

    $response = $this->get('/test');

    $response->assertOk();
});

test('auth with verified email', function () {
    Route::get('/test', fn (): Response => response(status: 200))
        ->middleware(EnsureVerifiedEmailsForSignInUsers::class);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/test');

    $response->assertOk();
});

test('auth without verified email', function () {
    Route::get('/test', fn (): Response => response(status: 200))
        ->middleware(EnsureVerifiedEmailsForSignInUsers::class);

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->get('/test');

    $response->assertRedirect(route('verification.notice'));
});
