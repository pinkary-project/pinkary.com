<?php

declare(strict_types=1);

use App\Services\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

beforeEach(function (): void {
    Storage::fake();
});

it('resizes and stores non-gif images', function (): void {
    $image = UploadedFile::fake()->image('test.jpg', 1200, 1200);

    $path = app(ImageProcessor::class)->process($image);

    expect($path)->toBeString();

    Storage::disk()->assertExists($path);

    $processedImage = ImageManager::imagick()->read(Storage::disk()->path($path));

    expect($processedImage->width())->toBeLessThanOrEqual(750)
        ->and($processedImage->height())->toBeLessThanOrEqual(750);
});

it('stores gifs without resizing them', function (): void {
    $image = UploadedFile::fake()->image('test.gif', 1200, 1200);

    $path = app(ImageProcessor::class)->process($image);

    expect($path)->toBeString();

    Storage::disk()->assertExists($path);

    $processedImage = ImageManager::imagick()->read(Storage::disk()->path($path));

    expect($processedImage->width())->toBe(1200)
        ->and($processedImage->height())->toBe(1200);
});

it('validates and deletes tracked image paths', function (): void {
    $path = UploadedFile::fake()->image('tracked.png')->store('images');
    $processor = app(ImageProcessor::class);

    expect($processor->isValid($path))->toBeTrue()
        ->and($processor->delete($path))->toBeTrue()
        ->and($processor->delete('uploads/image.png'))->toBeFalse();

    Storage::disk()->assertMissing($path);
});
