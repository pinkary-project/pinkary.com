<?php

declare(strict_types=1);

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\QuestionController;
use App\Http\Middleware\EnsureVerifiedEmailsForSignInUsers;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');
Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/support', 'support')->name('support');
Route::view('/status', 'status')->name('status');

Route::prefix('/@{user:username}')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])
        ->name('profile.show')
        ->middleware(EnsureVerifiedEmailsForSignInUsers::class);

    Route::get('questions/{question}', [QuestionController::class, 'show'])
        ->name('questions.show')
        ->middleware(EnsureVerifiedEmailsForSignInUsers::class);
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::view('home', 'home')->name('home');
    Route::view('explore', 'explore')->name('explore');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}', [NotificationController::class, 'show'])
        ->name('notifications.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/qr-code', QrCodeController::class)->name('qr-code.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
