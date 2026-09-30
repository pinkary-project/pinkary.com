<?php

declare(strict_types=1);

use App\Rules\ImageUpload;
use Illuminate\Http\UploadedFile;

/**
 * @param  list<string>  $reported
 */
function failureRecorder(array &$reported): Closure
{
    return function (string $message) use (&$reported): void {
        $reported[] = $message;
    };
}

test('a value that is not an upload is left to the file rule to report', function (): void {
    $reported = [];

    // ImageUpload runs after File::image() in the same chain, which bails first.
    (new ImageUpload)->validate('image', 'https://example.com/a.png', failureRecorder($reported));

    expect($reported)->toBeEmpty();
});

test('an image whose dimensions cannot be read is reported rather than divided by', function (): void {
    $reported = [];

    (new ImageUpload)->validate('image', UploadedFile::fake()->create('notes.txt', 1), failureRecorder($reported));

    // getimagesize() returns false, so dimensions() is not an array.
    expect($reported)->toBe(['The image aspect ratio could not be determined.']);
});

test('an image narrower than two fifths of its height is rejected', function (): void {
    $reported = [];

    (new ImageUpload)->validate('image', UploadedFile::fake()->image('tall.jpg', 100, 900), failureRecorder($reported));

    expect($reported)->toBe(['The image aspect ratio must be less than 2/5.']);
});

test('a normally shaped image passes', function (): void {
    $reported = [];

    (new ImageUpload)->validate('image', UploadedFile::fake()->image('square.jpg', 400, 400), failureRecorder($reported));

    expect($reported)->toBeEmpty();
});

test('the rule chain bails and its messages cover the whole chain', function (): void {
    // File expands into image/mimes/max/dimensions; bail keeps one bad file
    // from producing four complaints.
    expect(ImageUpload::rules()[0])->toBe('bail')
        ->and(ImageUpload::messages())->toHaveKeys(['image', 'mimes', 'max', 'dimensions']);
});
