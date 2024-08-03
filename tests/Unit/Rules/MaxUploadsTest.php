<?php

declare(strict_types=1);

use App\Rules\MaxUploads;
use Illuminate\Http\UploadedFile;

test('accepts an upload limit', function () {
    $rule = new MaxUploads(1);

    expect($rule->maxUploads)->toBe(1);
});

test('passes when the number of uploads is less than the limit', function () {
    Storage::fake('public');
    $rule = new MaxUploads(1);

    $image = UploadedFile::fake()->image('image.jpg');

    $rule->validate('image', [$image], function () {
        $this->fail('The validation callback should not be called.');
    });

    expect(true)->toBeTrue();
});

test('fails when the number of uploads is greater than the limit', function () {
    Storage::fake('public');
    $rule = new MaxUploads(1);

    $image1 = UploadedFile::fake()->image('image1.jpg');
    $image2 = UploadedFile::fake()->image('image2.jpg');

    $rule->validate('image', [$image1, $image2], function (string $errorMessage) {
        expect($errorMessage)->toBe('You can only upload 1 images.');
    });
});
