<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Account\AccountController;
use App\Http\Controllers\Api\Account\AvatarController;
use App\Http\Controllers\Api\Account\EmailVerificationController;
use App\Http\Controllers\Api\Account\PasswordController;
use App\Http\Controllers\Api\Account\TwoFactorController;
use App\Http\Controllers\Api\Account\TwoFactorRecoveryCodeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::prefix('account')->as('account.')->group(function (): void {
        Route::get('email-verification/{user}/{hash}', [EmailVerificationController::class, 'complete'])
            ->middleware(['signed:relative', 'throttle:6,1'])
            ->name('email-verification.complete');

        Route::post('password/recovery', [PasswordController::class, 'recovery'])
            ->middleware('throttle:6,1')
            ->name('password.recovery');

        Route::post('password/reset', [PasswordController::class, 'reset'])
            ->middleware('throttle:6,1')
            ->name('password.reset');

        Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
            Route::post('avatar/refresh', [AvatarController::class, 'refresh'])
                ->middleware('throttle:10,1')
                ->name('avatar.refresh');
            Route::delete('avatar', [AvatarController::class, 'destroy'])
                ->middleware('throttle:10,1')
                ->name('avatar.destroy');
        });

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('email-verification', [EmailVerificationController::class, 'show'])
                ->name('email-verification.show');
            Route::post('email-verification/notification', [EmailVerificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('email-verification.notification');

            Route::put('password', [PasswordController::class, 'update'])
                ->middleware('throttle:6,1')
                ->name('password.update');

            Route::get('two-factor', [TwoFactorController::class, 'show'])
                ->name('two-factor.show');
            Route::post('two-factor', [TwoFactorController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('two-factor.store');
            Route::post('two-factor/confirm', [TwoFactorController::class, 'confirm'])
                ->middleware('throttle:6,1')
                ->name('two-factor.confirm');
            Route::delete('two-factor', [TwoFactorController::class, 'destroy'])
                ->middleware('throttle:6,1')
                ->name('two-factor.destroy');

            Route::post('two-factor/recovery-codes', [TwoFactorRecoveryCodeController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('two-factor.recovery-codes.show');
            Route::put('two-factor/recovery-codes', [TwoFactorRecoveryCodeController::class, 'update'])
                ->middleware('throttle:6,1')
                ->name('two-factor.recovery-codes.update');

            Route::delete('/', [AccountController::class, 'destroy'])
                ->middleware('throttle:6,1')
                ->name('destroy');
            Route::delete('tokens', [AccountController::class, 'revokeTokens'])
                ->middleware('throttle:6,1')
                ->name('tokens.destroy');
        });
    });
});
