<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Mockery;

beforeEach(function () {
    Storage::fake('public');
    $this->image = UploadedFile::fake()->image(
        name: 'test.jpg',
        width: 1000,
        height: 1000,
    )->size(6 * 1024);
    $this->path = $this->image->store('images', 'public');
    $this->file = Storage::disk('public')->path($this->path);
});

test('optimize image', function () {
    $sizeBefore = $this->image->getSize();

    ImageOptimizer::optimize(
        path: $this->path,
        width: 500,
        height: 500,
        quality: 80,
    );

    $imagick = new Imagick($this->file);

    expect(file_exists(Storage::disk('public')->path($this->path)))->toBeTrue()
        ->and($imagick->getImageWidth())->toBe(500)
        ->and($imagick->getImageHeight())->toBe(500)
        ->and(File::size($this->file))->toBeLessThan($sizeBefore);
});

test('optimize thumbnail', function () {
    $sizeBefore = $this->image->getSize();

    ImageOptimizer::optimize(
        path: $this->path,
        width: 100,
        height: 100,
        quality: 80,
        isThumbnail: true
    );

    $imagick = new Imagick($this->file);

    expect(File::exists($this->file))->toBeTrue()
        ->and($imagick->getImageWidth())->toBe(100)
        ->and($imagick->getImageHeight())->toBe(100)
        ->and(File::size($this->file))->toBeLessThan($sizeBefore);
});

test('ensure orientation is maintained', function () {
    $orientationBefore = (new Imagick($this->file))->getImageOrientation();

    ImageOptimizer::optimize(
        path: $this->path,
        width: 100,
        height: 100,
        quality: 80,
        isThumbnail: true
    );

    $orientationAfter = (new Imagick($this->file))->getImageOrientation();

    expect($orientationAfter)->toBe($orientationBefore);
});

test('it optimizes an image', function () {
    $imagickMock = Mockery::mock(Imagick::class);
    $imagickMock->shouldReceive('resizeImage')->once();
    $imagickMock->shouldReceive('autoOrient')->once();
    $imagickMock->shouldReceive('stripImage')->once();
    $imagickMock->shouldReceive('setImageCompressionQuality')->once();
    $imagickMock->shouldReceive('writeImage')->once();
    $imagickMock->shouldReceive('clear')->once();
    $imagickMock->shouldReceive('destroy')->once();

    new ImageOptimizer(
        path: $this->path,
        width: 200,
        height: 200,
        quality: 80,
        isThumbnail: false,
        instance: $imagickMock
    );

    $imagickMock->shouldHaveReceived('resizeImage');
    $imagickMock->shouldHaveReceived('autoOrient');
    $imagickMock->shouldHaveReceived('stripImage');
    $imagickMock->shouldHaveReceived('setImageCompressionQuality');
    $imagickMock->shouldHaveReceived('writeImage');
    $imagickMock->shouldHaveReceived('clear');
    $imagickMock->shouldHaveReceived('destroy');
});

test('it optimizes a thumbnail image', function () {
    $imagickMock = Mockery::mock(Imagick::class);
    $imagickMock->shouldReceive('getImageWidth')->andReturn(300);
    $imagickMock->shouldReceive('getImageHeight')->andReturn(300);
    $imagickMock->shouldReceive('cropImage')->once();
    $imagickMock->shouldReceive('setImagePage')->once();
    $imagickMock->shouldReceive('autoOrient')->once();
    $imagickMock->shouldReceive('resizeImage')->once();
    $imagickMock->shouldReceive('stripImage')->once();
    $imagickMock->shouldReceive('setImageCompressionQuality')->once();
    $imagickMock->shouldReceive('writeImage')->once();
    $imagickMock->shouldReceive('clear')->once();
    $imagickMock->shouldReceive('destroy')->once();

    new ImageOptimizer(
        path: $this->path,
        width: 150,
        height: 150,
        quality: 80,
        isThumbnail: true,
        instance: $imagickMock
    );

    $imagickMock->shouldHaveReceived('getImageWidth');
    $imagickMock->shouldHaveReceived('getImageHeight');
    $imagickMock->shouldHaveReceived('cropImage');
    $imagickMock->shouldHaveReceived('setImagePage');
    $imagickMock->shouldHaveReceived('resizeImage');
    $imagickMock->shouldHaveReceived('autoOrient');
    $imagickMock->shouldHaveReceived('stripImage');
    $imagickMock->shouldHaveReceived('setImageCompressionQuality');
    $imagickMock->shouldHaveReceived('writeImage');
    $imagickMock->shouldHaveReceived('clear');
    $imagickMock->shouldHaveReceived('destroy');
});
