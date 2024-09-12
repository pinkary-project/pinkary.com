<?php

declare(strict_types=1);

use App\Http\Controllers\BookmarksController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\HashtagController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UserAvatarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGitHubUsernameController;
use App\Http\Controllers\UserIsVerifiedController;
use App\Http\Controllers\UserTimezoneController;
use App\Http\Middleware\EnsureVerifiedEmailsForSignInUsers;
use Illuminate\Support\Facades\Route;

Route::view('/about', 'about')->name('about');

Route::view('/', 'home/feed')->name('home.feed');
Route::redirect('/for-you', '/following')->name('home.for_you');
Route::view('/following', 'home/following')->name('home.following');
Route::view('/trending', 'home/trending-questions')->name('home.trending');
Route::view('/users', 'home/users')->name('home.users');

Route::get('/hashtag/{hashtag}', HashtagController::class)->name('hashtag.show');

Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/support', 'support')->name('support');
Route::view('/brand/resources', 'brand.resources')->name('brand.resources');

Route::redirect('/sponsors', 'https://github.com/sponsors/nunomaduro/')->name('sponsors');

Route::get('/changelog', [ChangelogController::class, 'show'])->name('changelog');
Route::post('/profile/timezone', [UserTimezoneController::class, 'update'])->name('profile.timezone.update');

Route::prefix('/@{username}')->group(function () {
    Route::get('/', [UserController::class, 'show'])
        ->name('profile.show')
        ->middleware(EnsureVerifiedEmailsForSignInUsers::class);

    Route::get('questions/{question}', [QuestionController::class, 'show'])
        ->name('questions.show')
        ->middleware(EnsureVerifiedEmailsForSignInUsers::class);
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('bookmarks', [BookmarksController::class, 'index'])->name('bookmarks.index');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}', [NotificationController::class, 'show'])
        ->name('notifications.show');

    Route::patch('/profile/avatar', [UserAvatarController::class, 'update'])
        ->name('profile.avatar.update');
    Route::delete('/profile/avatar', [UserAvatarController::class, 'destroy'])
        ->name('profile.avatar.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/qr-code', QrCodeController::class)->name('qr-code.image');

    Route::get('/profile', [UserController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [UserController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [UserController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('/profile/connect/github')->group(function () {
        Route::get('/', [UserGitHubUsernameController::class, 'index'])
            ->name('profile.connect.github');

        Route::get('/update', [
            UserGitHubUsernameController::class, 'update',
        ])->name('profile.connect.github.update');

        Route::delete('/', [
            UserGitHubUsernameController::class, 'destroy',
        ])->name('profile.connect.github.destroy');
    });

    Route::post('/profile/verified', [UserIsVerifiedController::class, 'update'])
        ->name('profile.verified.update');
});

Route::get('/telegram', function () {
    return redirect('https://t.me/+Yv9CUTC1q29lNzg8');
})->name('telegram');

require __DIR__.'/auth.php';
