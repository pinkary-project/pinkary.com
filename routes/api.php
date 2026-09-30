<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\LinkClickController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\LinkSortController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationReadController;
use App\Http\Controllers\Api\PollVoteController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuestionAnswerController;
use App\Http\Controllers\Api\QuestionBookmarkController;
use App\Http\Controllers\Api\QuestionCommentController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\QuestionIgnoreController;
use App\Http\Controllers\Api\QuestionLikeController;
use App\Http\Controllers\Api\QuestionPinController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserFollowController;
use App\Http\Controllers\Api\UserFollowerController;
use App\Http\Controllers\Api\UserFollowingController;
use App\Http\Controllers\Api\UserQrCodeController;
use App\Http\Controllers\Api\UserQuestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::prefix('auth')->as('auth.')->group(function (): void {
        Route::post('register', [RegisterController::class, 'store'])
            ->middleware('throttle:5,1,register')
            ->name('register');
        Route::post('login', [LoginController::class, 'store'])
            ->middleware('throttle:login')
            ->name('login');
    });

    Route::get('feed', [FeedController::class, 'index'])
        ->middleware(['optional.sanctum', 'throttle:60,1,feed'])
        ->name('feed.index');

    Route::get('users/{user:username}/qr-code', [UserQrCodeController::class, 'show'])
        ->middleware('throttle:60,1,qr')
        ->name('users.qr-code');

    // A public profile's links are tappable by anyone, including guests:
    // the web mounts the same Livewire component on profile/show.blade.php
    // and its non-owner branch calls click() with auth()->id(), which is
    // null for a guest. The Action already declines to count the link's
    // own owner, and dedupes per IP per day, so this is not a free counter
    // to inflate. Kept out of the shared `read` bucket: it is a POST, and
    // spends its own budget instead of a guest's read allowance.
    Route::post('links/{link}/click', [LinkClickController::class, 'store'])
        ->middleware(['optional.sanctum', 'throttle:60,1,link_click'])
        ->name('links.click');

    // Public reads. The web serves profiles, posts, comments, follower
    // lists and search to guests, so the API must too: `optional.sanctum`
    // lets a bearer token through when present (filling in viewer state
    // like liked/bookmarked/followed_by_me) without requiring one. These
    // share the `read` bucket rather than the 120/min `api` write bucket.
    // `feed` and the QR code keep their own, larger dedicated budgets
    // because they are the heaviest and most cacheable public reads.
    Route::middleware(['optional.sanctum', 'throttle:60,1,read'])->group(function (): void {
        Route::get('questions/{question}', [QuestionController::class, 'show'])
            ->name('questions.show')
            ->whereUuid('question');

        Route::get('questions/{question}/comments', [QuestionCommentController::class, 'index'])
            ->name('questions.comments.index')
            ->whereUuid('question');

        Route::get('users/{user:username}', [UserController::class, 'show'])
            ->name('users.show');

        Route::get('users/{user:username}/questions', [UserQuestionController::class, 'index'])
            ->name('users.questions.index');

        Route::get('users/{user:username}/followers', [UserFollowerController::class, 'index'])
            ->name('users.followers.index');

        Route::get('users/{user:username}/following', [UserFollowingController::class, 'index'])
            ->name('users.following.index');

        Route::get('search', [SearchController::class, 'index'])
            ->middleware('throttle:30,1,search')
            ->name('search.index');
    });

    Route::middleware(['auth:sanctum', 'throttle:120,1,api'])->group(function (): void {
        Route::post('auth/logout', [LogoutController::class, 'destroy'])
            ->name('auth.logout');

        Route::get('profile', [ProfileController::class, 'show'])
            ->name('profile.show');

        Route::patch('profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::delete('questions/{question}', [QuestionController::class, 'destroy'])
            ->name('questions.destroy')
            ->whereUuid('question');

        // The liker list stays owner-only (QuestionPolicy::viewLikes) and
        // authenticated: it is not a public surface on the web either.
        Route::get('questions/{question}/likes', [QuestionLikeController::class, 'index'])
            ->name('questions.likes.index')
            ->whereUuid('question');

        Route::apiResource('channels', ChannelController::class)
            ->only('index');

        Route::post('users/{user:username}/follow', [UserFollowController::class, 'store'])
            ->middleware('throttle:15,1,follow')
            ->name('users.follow');

        Route::delete('users/{user:username}/follow', [UserFollowController::class, 'destroy'])
            ->name('users.unfollow');

        Route::apiResource('links', LinkController::class)
            ->only(['store', 'update', 'destroy']);

        Route::post('links/sort', [LinkSortController::class, 'store'])
            ->name('links.sort');

        // The verified-email gate the web applies and the API did not.
        //
        // On the web it is applied twice: as middleware on the bookmark and
        // notification reads (routes/web.php:63) and as a
        // NeedsVerifiedEmail check inside every state-changing Livewire
        // action -- posting, answering, commenting, liking, bookmarking,
        // pinning, ignoring, voting and image upload. The API had no
        // equivalent anywhere, so a brand new unverified account -- exactly
        // the population this gate exists for, and the one
        // DeleteNonEmailVerifiedUsersCommand only culls after 24h -- could
        // do all of it from the app while the site refused every action.
        Route::middleware('verified')->group(function (): void {
            Route::post('questions', [QuestionController::class, 'store'])
                ->name('questions.store');

            Route::put('questions/{question}/answer', [QuestionAnswerController::class, 'update'])
                ->name('questions.answer.update')
                ->whereUuid('question');

            Route::post('questions/{question}/pin', [QuestionPinController::class, 'store'])
                ->name('questions.pin')
                ->whereUuid('question');

            Route::delete('questions/{question}/pin', [QuestionPinController::class, 'destroy'])
                ->name('questions.unpin')
                ->whereUuid('question');

            Route::post('questions/{question}/ignore', [QuestionIgnoreController::class, 'store'])
                ->name('questions.ignore')
                ->whereUuid('question');

            Route::apiResource('questions.comments', QuestionCommentController::class)
                ->only('store')
                ->whereUuid('question');

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

            Route::post('questions/{question}/poll/vote', [PollVoteController::class, 'store'])
                ->name('questions.poll.vote')
                ->whereUuid('question');

            // Composer images. The web reached this through Livewire's file
            // upload, which an API client cannot speak; the storage, the
            // limits and the rendering were all here already, so this opens
            // the same pipeline over HTTP. Its own bucket because a request
            // can carry several megabytes -- it should not spend the
            // caller's write budget on a request the write endpoints are
            // meant to answer quickly.
            Route::post('images', [ImageController::class, 'store'])
                ->middleware('throttle:20,1,image')
                ->name('images.store');

            Route::apiResource('bookmarks', BookmarkController::class)
                ->only('index');

            Route::apiResource('notifications', NotificationController::class)
                ->only(['index', 'destroy']);

            Route::post('notifications/read', [NotificationReadController::class, 'store'])
                ->name('notifications.read');
        });
    });
});
