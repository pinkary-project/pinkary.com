<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Api\CaptchaController;
use App\Http\Controllers\Api\CaptchaResultController;
use App\Http\Controllers\Api\CaptchaWidgetController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\LinkClickController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\LinkSortController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserQrCodeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::prefix('captcha')->as('captcha.')->middleware(['optional.sanctum', 'throttle:30,1,captcha'])->group(function (): void {
        Route::get('/', [CaptchaController::class, 'show'])->name('show');
        Route::get('widget', CaptchaWidgetController::class)->name('widget');
        Route::get('result', CaptchaResultController::class)->name('result');
    });

    Route::prefix('auth')->as('auth.')->group(function (): void {
        Route::post('register', [RegisterController::class, 'store'])
            ->middleware('throttle:5,1,register')
            ->name('register');
        Route::post('login', [LoginController::class, 'store'])
            ->middleware('throttle:login')
            ->name('login');
        // An unauthenticated six-digit challenge needs its own brute-force budget.
        Route::post('login/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
            ->middleware('throttle:two-factor-challenge')
            ->name('login.two-factor-challenge');
    });

    Route::get('users/{user:username}/qr-code', [UserQrCodeController::class, 'show'])
        ->middleware('throttle:60,1,qr')
        ->name('users.qr-code');

    Route::post('links/{link}/click', [LinkClickController::class, 'store'])
        ->middleware(['optional.sanctum', 'throttle:60,1,link_click'])
        ->name('links.click');

    Route::middleware(['auth:sanctum', 'throttle:120,1,api'])->group(function (): void {
        Route::post('auth/logout', [LogoutController::class, 'destroy'])
            ->name('auth.logout');

        Route::get('profile', [ProfileController::class, 'show'])
            ->name('profile.show');

        Route::patch('profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::apiResource('channels', ChannelController::class)
            ->only('index');

        Route::apiResource('links', LinkController::class)
            ->only(['store', 'update', 'destroy']);

        Route::post('links/sort', [LinkSortController::class, 'store'])
            ->name('links.sort');
    });
});
