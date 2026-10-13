<?php

declare(strict_types=1);

use App\Models\User;
use App\Rules\ImageUpload;
use App\Services\ImageProcessor;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake();
});

/**
 * allFiles() includes ImageProcessor's dated subdirectories.
 *
 * @return array<int, string>
 */
function storedImages(): array
{
    return Storage::disk()->allFiles('images');
}

test('a guest cannot upload a composer image', function (): void {
    $this->post(route('api.v1.images.store'), [
        'images' => [UploadedFile::fake()->image('photo.jpg', 800, 600)],
    ])->assertUnauthorized();
});

test('an image is stored and handed back with a path and a public url', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $response = $this->post(route('api.v1.images.store'), [
        'images' => [UploadedFile::fake()->image('holiday.jpg', 1200, 900)],
    ], $headers)->assertCreated()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.count', 1)
        ->assertJsonPath('data.0.original_name', 'holiday.jpg');

    $path = $response->json('data.0.path');

    expect($path)->toBeString();

    Storage::disk()->assertExists($path);

    expect($path)->toStartWith('images/')
        ->and($response->json('data.0.url'))->toBe(app(ImageProcessor::class)->url($path));
});

test('several images upload in one request and gifs skip resizing', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->post(route('api.v1.images.store'), [
        'images' => [
            UploadedFile::fake()->image('one.jpg', 1000, 1000),
            UploadedFile::fake()->image('two.gif', 1000, 1000),
        ],
    ], $headers)->assertCreated()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.count', 2)
        ->assertJsonPath('data.1.original_name', 'two.gif');

    expect(storedImages())->toHaveCount(2);
});

test('a post may not carry more than the web allows', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $images = [];
    for ($i = 0; $i <= ImageUpload::MAX_PER_POST; $i++) {
        $images[] = UploadedFile::fake()->image("image-{$i}.jpg", 800, 600);
    }

    $this->post(route('api.v1.images.store'), ['images' => $images], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('images');

    expect(storedImages())->toBeEmpty();
});

test('a non-image is rejected', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $response = $this->post(route('api.v1.images.store'), [
        'images' => [UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')],
    ], $headers)->assertUnprocessable()
        ->assertJsonValidationErrors('images.0');

    expect($response->json('errors')['images.0'])->toContain('The file must be an image.')
        ->and(storedImages())->toBeEmpty();
});

test('a too-narrow image is rejected on aspect ratio', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $response = $this->post(route('api.v1.images.store'), [
        'images' => [UploadedFile::fake()->image('tall.jpg', 100, 600)],
    ], $headers)->assertUnprocessable()
        ->assertJsonValidationErrors('images.0');

    expect($response->json('errors')['images.0'])
        ->toBe(['The image aspect ratio must be less than 2/5.'])
        ->and(storedImages())->toBeEmpty();
});

test('an oversized image is rejected', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->post(route('api.v1.images.store'), [
        'images' => [UploadedFile::fake()->create('huge.jpg', ImageUpload::MAX_KILOBYTES + 1, 'image/jpeg')],
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('images.0');

    expect(storedImages())->toBeEmpty();
});

test('an image past the dimension ceiling is rejected', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->post(route('api.v1.images.store'), [
        'images' => [UploadedFile::fake()->image('wide.jpg', ImageUpload::MAX_WIDTH + 200, 900)],
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('images.0');

    expect(storedImages())->toBeEmpty();
});

test('a request with no image is rejected', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.images.store'), [], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('images');
});

test('a storage failure is reported rather than returning a half-built image', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    // Model a silent failed write on disks configured with throw=false.
    $disk = Mockery::mock(Filesystem::class);
    $disk->shouldReceive('put')->once()->andReturnFalse();

    $storage = Mockery::mock(FilesystemManager::class);
    $storage->shouldReceive('disk')->andReturn($disk);

    $this->app->instance(FilesystemManager::class, $storage);

    $this->post(route('api.v1.images.store'), [
        'images' => [UploadedFile::fake()->image('photo.jpg', 800, 600)],
    ], $headers)
        ->assertStatus(422)
        ->assertJsonPath('message', 'The image could not be uploaded.');

    expect(storedImages())->toBeEmpty();
});
