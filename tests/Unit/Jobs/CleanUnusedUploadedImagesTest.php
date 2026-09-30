<?php

declare(strict_types=1);

use App\Jobs\CleanUnusedUploadedImages;
use App\Models\Question;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

it('caches the last run time', function (): void {
    CleanUnusedUploadedImages::dispatchSync();
    expect(Cache::get('clean_unused_uploaded_images_last_run'))
        ->toBeInstanceOf(CarbonImmutable::class);
});

it('ignores questions with a null answer', function (): void {
    Storage::fake();
    $day = now()->format('Y-m-d');

    $file = UploadedFile::fake()->image('image.jpg');
    $path = $file->store("images/{$day}");

    Question::factory()->create([
        'content' => "![Image]({$path})",
        'answer' => null,
        'created_at' => now()->subMinutes(10),
    ]);

    CleanUnusedUploadedImages::dispatchSync();

    Storage::disk()->assertExists($path);
});

it('cleans up unused images', function (): void {
    Storage::fake();
    $day = now()->format('Y-m-d');

    $file1 = UploadedFile::fake()->image('image1.jpg');
    $file2 = UploadedFile::fake()->image('image2.jpg');
    $file3 = UploadedFile::fake()->image('image3.jpg');

    $path1 = $file1->store("images/{$day}");
    $path2 = $file2->store("images/{$day}");
    $path3 = $file3->store("images/{$day}");

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

    CleanUnusedUploadedImages::dispatchSync();

    expect(Storage::disk()->allFiles())->not->toContain($path3);

    Storage::disk()->assertExists($path1);
    Storage::disk()->assertExists($path2);
    Storage::disk()->assertMissing($path3);
});

it('keeps an image a mobile post referenced by its absolute url', function (): void {
    // The mobile API hands clients an absolute URL to embed, so a post
    // written from the app stores that instead of the bare disk path.
    Storage::fake();
    $day = now()->format('Y-m-d');

    $path = UploadedFile::fake()->image('image.jpg')->store("images/{$day}");
    $absolute = Storage::disk()->url($path);

    Question::factory()->create([
        'content' => "![Image]({$absolute})",
        'answer' => null,
        'created_at' => now()->subMinutes(10),
    ]);

    CleanUnusedUploadedImages::dispatchSync();

    Storage::disk()->assertExists($path);
});

it('normalizes both reference styles to the same disk relative path', function (): void {
    $questions = Question::factory()->create([
        'content' => '![a](images/2026-09-29/a.jpg) ![b](https://cdn.example.com/images/2026-09-29/b.jpg)',
        'answer' => '![c](/storage/images/2026-09-29/c.jpg)',
        'created_at' => now(),
    ]);

    $extracted = (new CleanUnusedUploadedImages)->extractImagesFrom(
        new Illuminate\Database\Eloquent\Collection([$questions])
    );

    expect($extracted)->toContain('images/2026-09-29/a.jpg')
        ->toContain('images/2026-09-29/b.jpg')
        ->toContain('images/2026-09-29/c.jpg')
        ->toHaveCount(3);
});
