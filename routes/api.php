<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->as('api.')->group(static function (): void {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
});
