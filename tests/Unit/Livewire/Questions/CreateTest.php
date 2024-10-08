<?php

declare(strict_types=1);

use App\Livewire\Questions\Create;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

test('render', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->assertOk()->assertSee('Ask a question...');
});

test('refreshes when link settings changes', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->assertSeeHtml('text-blue-500');

    $user->update([
        'settings' => [
            'link_shape' => 'rounded-lg',
            'gradient' => 'from-red-500 to-purple-600',
        ],
    ]);

    $component->dispatch('link-settings.updated');

    $component->assertSeeHtml('text-red-500');
});

test('store', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    expect(App\Models\Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');
    $component->assertSet('content', '');
    $component->assertSet('anonymously', true);

    $component->assertDispatched('notification.created', message: 'Question sent.');
    $component->assertDispatched('question.created');

    $question = App\Models\Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeTrue()
        ->and($question->parent_id)->toBeNull()
        ->and($question->root_id)->toBeNull();
});

test('store auth', function () {
    $user = User::factory()->create();

    expect(App\Models\Question::count())->toBe(0);

    $component = Livewire::test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');

    $component->assertRedirect('login');

    expect(App\Models\Question::count())->toBe(0);
});

test('store rate limit', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    expect(App\Models\Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasNoErrors();

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasNoErrors();

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasNoErrors();

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 3 questions per minute.',
    ]);

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 3 questions per minute.',
    ]);
});

test('store comment', function () {
    $userA = User::factory()->create();

    $question = App\Models\Question::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userA->id,
        'parentId' => $question->id,
    ]);

    $this->travel(1)->seconds(); // To avoid time conflicts
    $component->set('content', 'My comment');

    $component->call('store');
    $component->assertSet('content', '');

    $component->assertDispatched('notification.created', message: 'Comment sent.');
    $component->assertDispatched('question.created');

    $comment = App\Models\Question::latest()->limit(1)->first();

    expect($comment->from_id)->toBe($userA->id)
        ->and($comment->to_id)->toBe($userA->id)
        ->and($comment->answer)->toBe('My comment')
        ->and($comment->parent_id)->toBe($question->id)
        ->and($comment->root_id)->toBe($question->id);
});

test('store comment on a comment', function () {
    $userA = User::factory()->create();

    $question = App\Models\Question::factory()->create();

    $questionWithComment = App\Models\Question::factory()->create([
        'to_id' => $userA->id,
        'parent_id' => $question->id,
        'root_id' => $question->id,
    ]);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userA->id,
        'parentId' => $questionWithComment->id,
    ]);

    $this->travel(1)->seconds(); // To avoid time conflicts

    $component->set('content', 'My comment');

    $component->call('store');
    $component->assertSet('content', '');

    $component->assertDispatched('notification.created', message: 'Comment sent.');
    $component->assertDispatched('question.created');

    $comment = App\Models\Question::latest()->limit(1)->first();

    expect($comment->from_id)->toBe($userA->id)
        ->and($comment->to_id)->toBe($userA->id)
        ->and($comment->answer)->toBe('My comment')
        ->and($comment->parent_id)->toBe($questionWithComment->id)
        ->and($comment->root_id)->toBe($questionWithComment->root_id)
        ->and($comment->root_id)->toBe($question->id);
});

test('max 30 questions per day', function () {
    $user = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    for ($i = 0; $i <= 30; $i++) {
        $component->set('content', 'Hello World');
        $component->call('store');
        $this->travelTo(now()->addMinutes($i));
        $component->assertHasNoErrors();
    }

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'You can only send 30 questions per day.',
    ]);
});

test('cannot store with blank characters', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    expect(App\Models\Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', "\u{200E}");
    $component->call('store');

    $component->assertHasErrors([
        'content' => 'The content field cannot contain blank characters.',
    ]);
});

test('store with user questions_preference set to public', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $userA->update(['prefers_anonymous_questions' => false]);

    expect(App\Models\Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');
    $component->assertSet('content', '');
    $component->assertSet('anonymously', false);

    $component->assertDispatched('notification.created', message: 'Question sent.');
    $component->assertDispatched('question.created');

    $question = App\Models\Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeFalse();
});

test('store with user questions_preference set to anonymously', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $userA->update(['prefers_anonymous_questions' => true]);

    expect(App\Models\Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');
    $component->assertSet('content', '');
    $component->assertSet('anonymously', true);

    $component->assertDispatched('notification.created', message: 'Question sent.');
    $component->assertDispatched('question.created');

    $question = App\Models\Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeTrue();
});

test('anonymous set back to user\'s preference after sending a question', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $userA->update(['prefers_anonymous_questions' => false]);

    expect(App\Models\Question::count())->toBe(0);

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('content', 'Hello World');
    $component->toggle('anonymously');

    $component->call('store');
    $component->assertSet('content', '');
    $component->assertSet('anonymously', false);

    $component->assertDispatched('notification.created', message: 'Question sent.');
    $component->assertDispatched('question.created');

    $question = App\Models\Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeTrue();
});

test('show "Share an update..." if user is viewing his own profile', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->assertSee('Share an update...');

    $user2 = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user2->id,
    ]);

    $component->assertSee('Ask a question...');
});

test('user don\'t see the anonymous checkbox if the user is viewing his own profile', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->assertDontSeeHtml('for="anonymously"');
});

test('user cannot share update anonymously', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Hello World');
    $component->set('anonymously', true);

    $component->call('store');

    $this->assertDatabaseHas('questions', [
        'from_id' => $user->id,
        'to_id' => $user->id,
        'answer' => 'Hello World',
        'content' => '__UPDATE__', // This is the content for an update
        'anonymously' => false,
    ]);
});

it('has a property for storing the images', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, [
            'toId' => $user->id,
        ]);

    expect($component->images)->toBeArray();
});

test('updated lifecycle method', function () {
    $user = User::factory()->create(['is_verified' => true]);

    $component = Livewire::actingAs($user)
        ->test(Create::class, [
            'toId' => $user->id,
        ]);
    expect($component->invade()->updated('images'))->toBeNull();
});

test('updated method invokes handleUploads', function () {
    Storage::fake('public');
    $user = User::factory()->create(['is_verified' => true]);
    $file = UploadedFile::fake()->image('photo1.jpg');
    $date = now()->format('Y-m-d');
    $path = $file->store("images/{$date}", 'public');

    $component = Livewire::actingAs($user)->test(Create::class);

    $component->set('images', [$file]);
    $component->invade()->updated('images');

    expect(session('images'))->toBeArray()
        ->and(session('images'))->toContain($path);

    $component->assertDispatched('image.uploaded');

    $component->assertSet('images', []);
});

test('unused image cleanup when store is called', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo1.jpg');
    $date = now()->format('Y-m-d');
    $path = $file->store("images/{$date}", 'public');

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);
    $component->set('images', [$file]);
    $component->call('uploadImages');

    Storage::disk('public')->assertExists($path);

    expect(session('images'))->toBeArray()
        ->and(session('images'))->toContain($path);

    $component->set('content', 'Hello World');
    $component->call('store');

    Storage::disk('public')->assertMissing($path);
    expect(session('images'))->toBeNull();
});

test('used images are NOT cleanup when store is called', function () {
    Storage::fake('public');
    $user = User::factory()->create(['is_verified' => true]);
    $file = UploadedFile::fake()->image('photo1.jpg');
    $name = $file->hashName();
    $date = now()->format('Y-m-d');
    $path = 'images/'.$date.'/'.$name;

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);
    $component->set('images', [$file]);

    Storage::disk('public')->assertExists($path);

    expect(session('images'))->toBeArray()
        ->and(session('images'))->toContain($path);

    $url = Storage::disk('public')->url($path);

    $component->set('content', "![Image Alt Text]({$url})");
    $component->call('store');

    Storage::disk('public')->assertExists($path);
    expect(session('images'))->toBeNull();
});

test('delete image', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo1.jpg');
    $path = $file->store('images', 'public');

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    Storage::disk('public')->assertExists($path);

    $component->call('deleteImage', $path);

    $pathAgain = $file->store('images', 'public');
    Storage::disk('public')->assertExists($pathAgain);

    $component->call('deleteImage', $pathAgain);

    Storage::disk('public')->assertMissing($pathAgain);
});

test('optimizeImage method resizes and saves the image', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $testImage = UploadedFile::fake()->image('test.jpg', 1200, 1200); // Larger than 1000x1000
    $path = $testImage->store('images', 'public');

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->call('optimizeImage', $path);

    Storage::disk('public')->assertExists($path);

    $optimizedImagePath = Storage::disk('public')->path($path);

    $originalImageSize = filesize($testImage->getPathname());
    $optimizedImageSize = filesize($optimizedImagePath);

    expect($optimizedImageSize)->toBeLessThan($originalImageSize);

    $manager = ImageManager::imagick();
    $image = $manager->read($optimizedImagePath);

    expect($image->width())->toBeLessThanOrEqual(1000)
        ->and($image->height())->toBeLessThanOrEqual(1000);
});

test('optimizeImage method resizes and saves image with multiple frames', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $gif = new Imagick();

    $gif->setFormat('gif');

    for ($i = 0; $i < 3; $i++) {
        $frame = new Imagick();

        $frame->newImage(1200, 1200, new ImagickPixel(match ($i) {
            0 => 'red',
            1 => 'green',
            2 => 'blue',
        }));

        $frame->setImageFormat('gif');

        $gif->addImage($frame);
    }

    $testImage = UploadedFile::fake()->createWithContent('test.gif', $gif->getImagesBlob());

    $path = $testImage->store('images', 'public');

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->call('optimizeImage', $path);

    Storage::disk('public')->assertExists($path);

    $optimizedImagePath = Storage::disk('public')->path($path);

    $originalImageSize = filesize($testImage->getPathname());
    $optimizedImageSize = filesize($optimizedImagePath);

    expect($optimizedImageSize)->toBeLessThan($originalImageSize);

    $optimizedImage = new Imagick($optimizedImagePath);

    expect($optimizedImage->getImageWidth())->toBeLessThanOrEqual(1000)
        ->and($optimizedImage->getImageHeight())->toBeLessThanOrEqual(1000)
        ->and($optimizedImage->getNumberImages())->toBe(3);

    $frames = $optimizedImage->coalesceImages();

    foreach ($frames as $frame) {
        expect($frame->getImageWidth())->toBeLessThanOrEqual(1000)
            ->and($frame->getImageHeight())->toBeLessThanOrEqual(1000);
    }
});

test('maxFileSize and maxImages', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    expect($component->maxFileSize)->toBe(1024 * 8)
        ->and($component->uploadLimit)->toBe(3);
});

test('non verified users can upload images', function () {
    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->image('test.jpg')]);
    $component->call('uploadImages');

    $component->assertHasNoErrors();
});

test('company verified users can upload images', function () {
    $user = User::factory()->create([
        'is_company_verified' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->image('test.jpg')]);
    $component->call('uploadImages');

    $component->assertHasNoErrors();
});

test('upload must be an image', function () {
    $user = User::factory()->create([
        'is_verified' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->create('test.pdf')]);
    $component->call('runImageValidation');

    $component->assertHasErrors([
        'images.0' => 'The file must be an image.',
    ]);
});

test('upload must be correct type of image', function () {
    $user = User::factory()->create([
        'is_verified' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->image('test.jpg')]);
    $component->call('runImageValidation');
    $component->assertHasNoErrors();

    $component->set('images', [UploadedFile::fake()->image('test.png')]);
    $component->call('runImageValidation');
    $component->assertHasNoErrors();

    $component->set('images', [UploadedFile::fake()->image('test.gif')]);
    $component->call('runImageValidation');
    $component->assertHasNoErrors();

    $component->set('images', [UploadedFile::fake()->image('test.webp')]);
    $component->call('runImageValidation');
    $component->assertHasNoErrors();

    $component->set('images', [UploadedFile::fake()->image('test.jpeg')]);
    $component->call('runImageValidation');
    $component->assertHasNoErrors();

    $component->set('images', [UploadedFile::fake()->image('test.bmp')]);
    $component->call('runImageValidation');

    expect($component->errors()->get('images.0'))->toBeArray()
        ->and($component->errors()->get('images.0'))->toContain('The image must be a file of type: jpeg, png, gif, webp, jpg.');
});

test('max file size error', function () {
    $user = User::factory()->create([
        'is_verified' => true,
    ]);

    $maxFileSize = 1024 * 8;

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $largeFile = UploadedFile::fake()->image('test.jpg')->size(1024 * 9);

    $component->set('images', [$largeFile]);
    $component->call('runImageValidation');

    expect($component->get('images'))
        ->toBeArray()
        ->and($component->get('images'))
        ->not()->toContain($largeFile);

    $component->assertHasErrors([
        'images.0' => "The image may not be greater than {$maxFileSize} kilobytes.",
    ]);
});

test('max size & ratio validation', function () {
    $user = User::factory()->create([
        'is_verified' => true,
    ]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [
        UploadedFile::fake()->image('test.jpg', '4005', '4005'),
    ]);
    $component->call('runImageValidation');

    $component->assertHasErrors([
        'images.0' => 'The image must be less than 4000 x 4000 pixels.',
    ]);

    $component->set('images', [
        UploadedFile::fake()->image('test.jpg', '429', '1100'),
    ]);
    $component->call('runImageValidation');

    $component->assertHasErrors([
        'images.0' => 'The image aspect ratio must be less than 2/5.',
    ]);
});
