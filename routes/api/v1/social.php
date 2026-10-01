<?php

declare(strict_types=1);

use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationReadController;
use App\Http\Controllers\Api\QuestionBookmarkController;
use App\Http\Controllers\Api\QuestionLikeController;
use App\Http\Controllers\Api\UserFollowController;
use App\Http\Controllers\Api\UserFollowerController;
use App\Http\Controllers\Api\UserFollowingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::middleware(['optional.sanctum', 'throttle:60,1,read'])->group(function (): void {
        Route::get('users/{user:username}/followers', [UserFollowerController::class, 'index'])
            ->name('users.followers.index');

        Route::get('users/{user:username}/following', [UserFollowingController::class, 'index'])
            ->name('users.following.index');
    });

    Route::middleware(['auth:sanctum', 'throttle:120,1,api'])->group(function (): void {
        // The liker list stays owner-only (QuestionPolicy::viewLikes) and
        // authenticated: it is not a public surface on the web either.
        Route::get('questions/{question}/likes', [QuestionLikeController::class, 'index'])
            ->name('questions.likes.index')
            ->whereUuid('question');

        Route::post('users/{user:username}/follow', [UserFollowController::class, 'store'])
            ->middleware('throttle:15,1,follow')
            ->name('users.follow');

        Route::delete('users/{user:username}/follow', [UserFollowController::class, 'destroy'])
            ->name('users.unfollow');

        Route::middleware('verified')->group(function (): void {
            Route::post('questions/{question}/like', [QuestionLikeController::class, 'store'])
                ->middleware('throttle:60,1,like')
                ->name('questions.like')
                ->whereUuid('question');

            Route::delete('questions/{question}/like', [QuestionLikeController::class, 'destroy'])
                ->name('questions.unlike')
                ->whereUuid('question');

            Route::post('questions/{question}/bookmark', [QuestionBookmarkController::class, 'store'])
                ->middleware('throttle:60,1,bookmark')
                ->name('questions.bookmark')
                ->whereUuid('question');

            Route::delete('questions/{question}/bookmark', [QuestionBookmarkController::class, 'destroy'])
                ->name('questions.unbookmark')
                ->whereUuid('question');

            Route::apiResource('bookmarks', BookmarkController::class)
                ->only('index');

            Route::apiResource('notifications', NotificationController::class)
                ->only(['index', 'destroy']);

            Route::post('notifications/read', [NotificationReadController::class, 'store'])
                ->name('notifications.read');
        });
    });
});
