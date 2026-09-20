<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Notifications\ReadController;
use App\Http\Controllers\Api\PollVotes\StoreController as StorePollVoteController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuestionBookmarks\DestroyController as DestroyQuestionBookmarkController;
use App\Http\Controllers\Api\QuestionBookmarks\StoreController as StoreQuestionBookmarkController;
use App\Http\Controllers\Api\QuestionComments\IndexController as IndexQuestionCommentsController;
use App\Http\Controllers\Api\QuestionComments\StoreController as StoreQuestionCommentController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\QuestionLikes\DestroyController as DestroyQuestionLikeController;
use App\Http\Controllers\Api\QuestionLikes\StoreController as StoreQuestionLikeController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserFollows\DestroyController as DestroyUserFollowController;
use App\Http\Controllers\Api\UserFollows\StoreController as StoreUserFollowController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::prefix('auth')->as('auth.')->group(function (): void {
        Route::post('register', RegisterController::class)
            ->middleware('throttle:5,1')
            ->name('register');
        Route::post('login', LoginController::class)
            ->middleware('throttle:login')
            ->name('login');
    });

    Route::get('feed', [FeedController::class, 'index'])
        ->middleware('optional.sanctum')
        ->name('feed.index');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', LogoutController::class)
            ->name('auth.logout');

        Route::get('profile', [ProfileController::class, 'show'])
            ->name('profile.show');

        Route::post('questions', [QuestionController::class, 'store'])
            ->name('questions.store');

        Route::get('questions/{question}', [QuestionController::class, 'show'])
            ->name('questions.show')
            ->whereUuid('question');

        Route::get('questions/{question}/comments', IndexQuestionCommentsController::class)
            ->name('questions.comments.index')
            ->whereUuid('question');

        Route::post('questions/{question}/comments', StoreQuestionCommentController::class)
            ->name('questions.comments.store')
            ->whereUuid('question');

        Route::post('questions/{question}/like', StoreQuestionLikeController::class)
            ->name('questions.like')
            ->whereUuid('question');

        Route::delete('questions/{question}/like', DestroyQuestionLikeController::class)
            ->name('questions.unlike')
            ->whereUuid('question');

        Route::post('questions/{question}/bookmark', StoreQuestionBookmarkController::class)
            ->name('questions.bookmark')
            ->whereUuid('question');

        Route::delete('questions/{question}/bookmark', DestroyQuestionBookmarkController::class)
            ->name('questions.unbookmark')
            ->whereUuid('question');

        Route::post('questions/{question}/poll/vote', StorePollVoteController::class)
            ->name('questions.poll.vote')
            ->whereUuid('question');

        Route::get('bookmarks', [BookmarkController::class, 'index'])
            ->name('bookmarks.index');

        Route::get('channels', [ChannelController::class, 'index'])
            ->name('channels.index');

        Route::get('search', [SearchController::class, 'index'])
            ->name('search.index');

        Route::get('notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::post('notifications/read', ReadController::class)
            ->name('notifications.read');

        Route::get('users/{user:username}', [UserController::class, 'show'])
            ->name('users.show');

        Route::post('users/{user:username}/follow', StoreUserFollowController::class)
            ->name('users.follow');

        Route::delete('users/{user:username}/follow', DestroyUserFollowController::class)
            ->name('users.unfollow');

        Route::post('links/{link}/click', LinkController::class)
            ->name('links.click');
    });
});
