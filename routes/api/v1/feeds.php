<?php

declare(strict_types=1);

use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserQuestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::get('feed', [FeedController::class, 'index'])
        ->middleware(['optional.sanctum', 'throttle:60,1,feed'])
        ->name('feed.index');

    Route::middleware(['optional.sanctum', 'throttle:60,1,read'])->group(function (): void {
        Route::get('users/{user:username}/questions', [UserQuestionController::class, 'index'])
            ->name('users.questions.index');

        Route::get('search', [SearchController::class, 'index'])
            ->middleware('throttle:30,1,search')
            ->name('search.index');
    });
});
