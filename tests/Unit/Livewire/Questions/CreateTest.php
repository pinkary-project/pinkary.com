<?php

declare(strict_types=1);

use App\Livewire\Questions\Create;
use App\Models\User;
use App\Rules\MaxUploads;
use App\Rules\VerifiedOnly;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\ImageFile;
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
        ->and($question->anonymously)->toBeTrue();
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
    $userB = User::factory()->create();

    $question = App\Models\Question::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userA->id,
        'parentId' => $question->id,
    ]);

    sleep(1);

    $component->set('content', 'My comment');

    $component->call('store');
    $component->assertSet('content', '');

    $component->assertDispatched('notification.created', message: 'Comment sent.');
    $component->assertDispatched('question.created');

    $comment = App\Models\Question::latest()->limit(1)->first();

    expect($comment->from_id)->toBe($userA->id)
        ->and($comment->to_id)->toBe($userA->id)
        ->and($comment->answer)->toBe('My comment')
        ->and($comment->parent_id)->toBe($question->id);
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

test('image property has correct validation rules', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, [
            'toId' => $user->id,
        ]);

    $rules = $component->invade()->rules();

    expect($rules)->toBeArray()
        ->and($rules['images'][0])->toBeInstanceOf(MaxUploads::class)
        ->and($rules['images'][1])->toBeInstanceOf(VerifiedOnly::class)
        ->and($rules['images.*'][0])->toBeInstanceOf(ImageFile::class);
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

    $component->dispatch('image.delete', ['path' => $path]);

    $pathAgain = $file->store('images', 'public');
    Storage::disk('public')->assertExists($pathAgain);

    $component->call('deleteImage', ['path' => $pathAgain]);

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

test('maxFileSize and maxImages', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    expect($component->maxFileSize)->toBe(1024 * 2)
        ->and($component->maxImages)->toBe(1);
});

test('only verified users can upload images', function () {
    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->image('test.jpg')]);
    $component->call('uploadImages');

    $component->assertHasErrors([
        'images' => 'This action is only available to verified users. Get verified in your profile settings.',
    ]);
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
