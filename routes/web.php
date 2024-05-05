<?php

declare(strict_types=1);

use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Profile\AvatarController;
use App\Http\Controllers\Profile\Connect\GitHubController;
use App\Http\Controllers\Profile\TimezoneController;
use App\Http\Controllers\Profile\VerifiedController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\QuestionController;
use App\Http\Middleware\EnsureVerifiedEmailsForSignInUsers;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::view('/feed', 'home/feed')->name('home.feed');
Route::view('/for-you', 'home/questions-for-you')->name('home.for_you');
Route::view('/trending', 'home/trending-questions')->name('home.trending');
Route::view('/users', 'home/users')->name('home.users');

Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/support', 'support')->name('support');
Route::view('/brand/resources', 'brand.resources')->name('brand.resources');

Route::redirect('/sponsors', 'https://github.com/sponsors/nunomaduro/')->name('sponsors');

Route::get('/changelog', [ChangelogController::class, 'show'])->name('changelog');
Route::post('/profile/timezone', [TimezoneController::class, 'update'])->name('profile.timezone.update');

Route::prefix('/@{username}')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])
        ->name('profile.show')
        ->middleware(EnsureVerifiedEmailsForSignInUsers::class);

    Route::get('questions/{question}', [QuestionController::class, 'show'])
        ->name('questions.show')
        ->middleware(EnsureVerifiedEmailsForSignInUsers::class);
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}', [NotificationController::class, 'show'])
        ->name('notifications.show');

    Route::patch('/profile/avatar', [AvatarController::class, 'update'])
        ->name('profile.avatar.update');
    Route::delete('/profile/avatar', [AvatarController::class, 'delete'])
        ->name('profile.avatar.delete');
});

Route::middleware('auth')->group(function () {
    Route::get('/qr-code', QrCodeController::class)->name('qr-code.image');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('/profile/connect/github')->group(function () {
        Route::get('/', [GitHubController::class, 'index'])
            ->name('profile.connect.github');

        Route::get('/update', [
            GitHubController::class, 'update',
        ])->name('profile.connect.github.update');

        Route::delete('/', [
            GitHubController::class, 'destroy',
        ])->name('profile.connect.github.destroy');
    });

    Route::post('/profile/verified', [VerifiedController::class, 'update'])
        ->name('profile.verified.update');
});

require __DIR__.'/auth.php';
