<?php

declare(strict_types=1);

use App\Console\Commands\MigrateFilesToS3Command;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('s3');
});

test('migrates avatar files from local to s3', function () {
    $local = Storage::disk('local');
    $local->put('avatars/test-avatar-1.png', 'avatar-content-1');
    $local->put('avatars/test-avatar-2.png', 'avatar-content-2');

    $this->artisan(MigrateFilesToS3Command::class, ['--disk' => 's3', '--force' => true])
        ->assertExitCode(0);

    $s3 = Storage::disk('s3');
    expect($s3->exists('avatars/test-avatar-1.png'))->toBeTrue()
        ->and($s3->exists('avatars/test-avatar-2.png'))->toBeTrue()
        ->and($s3->get('avatars/test-avatar-1.png'))->toBe('avatar-content-1')
        ->and($s3->get('avatars/test-avatar-2.png'))->toBe('avatar-content-2');
});

test('migrates image files from local to s3', function () {
    $local = Storage::disk('local');
    $local->put('images/2025-05-10/photo.jpg', 'image-content');
    $local->put('images/standalone.jpg', 'standalone-content');

    $this->artisan(MigrateFilesToS3Command::class, ['--disk' => 's3', '--force' => true])
        ->assertExitCode(0);

    $s3 = Storage::disk('s3');
    expect($s3->exists('images/2025-05-10/photo.jpg'))->toBeTrue()
        ->and($s3->exists('images/standalone.jpg'))->toBeTrue()
        ->and($s3->get('images/2025-05-10/photo.jpg'))->toBe('image-content');
});

test('skips files that already exist on s3 without overwrite', function () {
    $local = Storage::disk('local');
    $local->put('avatars/existing.png', 'new-content');

    $s3 = Storage::disk('s3');
    $s3->put('avatars/existing.png', 'old-content');

    $this->artisan(MigrateFilesToS3Command::class, ['--disk' => 's3', '--force' => true])
        ->assertExitCode(0);

    expect($s3->get('avatars/existing.png'))->toBe('old-content');
});

test('overwrites existing files when overwrite flag is set', function () {
    $local = Storage::disk('local');
    $local->put('avatars/existing.png', 'new-content');

    $s3 = Storage::disk('s3');
    $s3->put('avatars/existing.png', 'old-content');

    $this->artisan(MigrateFilesToS3Command::class, [
        '--disk' => 's3',
        '--force' => true,
        '--overwrite' => true,
    ])->assertExitCode(0);

    expect($s3->get('avatars/existing.png'))->toBe('new-content');
});

test('handles empty directories gracefully', function () {
    $this->artisan(MigrateFilesToS3Command::class, ['--disk' => 's3', '--force' => true])
        ->assertExitCode(0);
});

test('migrates both avatars and images in a single run', function () {
    $local = Storage::disk('local');
    $local->put('avatars/avatar.png', 'avatar-data');
    $local->put('images/photo.jpg', 'image-data');

    $this->artisan(MigrateFilesToS3Command::class, ['--disk' => 's3', '--force' => true])
        ->assertExitCode(0);

    $s3 = Storage::disk('s3');
    expect($s3->exists('avatars/avatar.png'))->toBeTrue()
        ->and($s3->exists('images/photo.jpg'))->toBeTrue();
});
