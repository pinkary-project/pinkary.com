<?php

declare(strict_types=1);

use App\Jobs\CleanupImages;
use App\Models\Question;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

it('caches the last run time', function () {
    CleanupImages::dispatchSync();
    expect(Cache::get('cleanup_images_last_run_time'))
        ->toBeInstanceOf(CarbonImmutable::class);
});

it('cleans up unused images', function () {
    Storage::fake('public');
    $day = now()->format('Y-m-d');

    $file1 = UploadedFile::fake()->image('image1.jpg');
    $file2 = UploadedFile::fake()->image('image2.jpg');
    $file3 = UploadedFile::fake()->image('image3.jpg');

    $path1 = Storage::disk('public')->putFile("images/{$day}", $file1);
    $path2 = $file2->store("images/{$day}", 'public');
    $path3 = $file3->store("images/{$day}", 'public');

    Question::factory(2)->sequence(
        [
            'content' => "![Image1]({$path1}) ![Image2]({$path2})",
            'created_at' => now()->subMinutes(10),
        ],
        [
            'content' => 'doesn\'t have an image',
            'is_ignored' => true,
            'created_at' => now()->subMinutes(10),
        ],
    )->create();

    CleanupImages::dispatchSync();

    expect(Storage::disk('public')->allFiles())->not->toContain($path3);

    Storage::disk('public')->assertExists($path1);
    Storage::disk('public')->assertExists($path2);
    Storage::disk('public')->assertMissing($path3);
});
