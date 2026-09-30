<?php

declare(strict_types=1);

use App\Http\Controllers\Api\QuestionCommentController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    // Public reads. `optional.sanctum` lets a bearer token through when present,
    // filling in viewer state like liked/bookmarked/followed_by_me without
    // requiring one. These share the `read` bucket rather than the 120/min
    // `api` write bucket; `feed` and the QR code keep their own, larger
    // budgets because they are the heaviest and most cacheable public reads.
    Route::middleware(['optional.sanctum', 'throttle:60,1,read'])->group(function (): void {
        Route::get('questions/{question}', [QuestionController::class, 'show'])
            ->name('questions.show')
            ->whereUuid('question');

        Route::get('questions/{question}/comments', [QuestionCommentController::class, 'index'])
            ->name('questions.comments.index')
            ->whereUuid('question');

        Route::get('users/{user:username}', [UserController::class, 'show'])
            ->name('users.show');
    });
});
