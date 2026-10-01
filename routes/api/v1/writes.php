<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\PollVoteController;
use App\Http\Controllers\Api\QuestionAnswerController;
use App\Http\Controllers\Api\QuestionCommentController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\QuestionIgnoreController;
use App\Http\Controllers\Api\QuestionPinController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::middleware(['auth:sanctum', 'throttle:120,1,api'])->group(function (): void {
        Route::delete('questions/{question}', [QuestionController::class, 'destroy'])
            ->name('questions.destroy')
            ->whereUuid('question');

        // The gate the web applies both as middleware on its bookmark and
        // notification reads and as a NeedsVerifiedEmail check inside every
        // state-changing Livewire action.
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

            Route::post('questions/{question}/poll/vote', [PollVoteController::class, 'store'])
                ->name('questions.poll.vote')
                ->whereUuid('question');

            // Composer images. Its own bucket because a request can carry several
            // megabytes and should not spend the caller's write budget.
            Route::post('images', [ImageController::class, 'store'])
                ->middleware('throttle:20,1,image')
                ->name('images.store');
        });
    });
});
