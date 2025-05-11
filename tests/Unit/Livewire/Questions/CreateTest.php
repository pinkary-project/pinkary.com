<?php

declare(strict_types=1);

use App\Livewire\Questions\Create;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake();
});

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

    expect(Question::count())->toBe(0);

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

    $question = Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeTrue()
        ->and($question->parent_id)->toBeNull()
        ->and($question->root_id)->toBeNull();
});

test('store auth', function () {
    $user = User::factory()->create();

    expect(Question::count())->toBe(0);

    $component = Livewire::test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Hello World');

    $component->call('store');

    $component->assertRedirect('login');

    expect(Question::count())->toBe(0);
});

test('store rate limit', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    expect(Question::count())->toBe(0);

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

    $question = Question::factory()->create();

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

    $comment = Question::latest()->limit(1)->first();

    expect($comment->from_id)->toBe($userA->id)
        ->and($comment->to_id)->toBe($userA->id)
        ->and($comment->answer)->toBe('My comment')
        ->and($comment->parent_id)->toBe($question->id)
        ->and($comment->root_id)->toBe($question->id);
});

test('store comment on a comment', function () {
    $userA = User::factory()->create();

    $question = Question::factory()->create();

    $questionWithComment = Question::factory()->create([
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

    $comment = Question::latest()->limit(1)->first();

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

    expect(Question::count())->toBe(0);

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

test('poll should have at least 2 options', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('isPoll', true);
    $component->set('pollOptions', ['Option 1']);
    $component->set('content', 'What is your favorite color?');

    $component->call('store');

    $component->assertHasErrors([
        'pollOptions' => 'A poll must have at least 2 options.',
    ]);
});

test('poll should have at most 4 options', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    /** @var Testable $component */
    $component = Livewire::actingAs($userA)->test(Create::class, [
        'toId' => $userB->id,
    ]);

    $component->set('isPoll', true);
    $component->set('pollOptions', ['Option 1', 'Option 2', 'Option 3', 'Option 4', 'Option 5']);
    $component->set('content', 'What is your favorite color?');

    $component->call('store');

    $component->assertHasErrors([
        'pollOptions' => 'A poll can have maximum 4 options.',
    ]);
});

test('poll button is visible only for shared updates', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id]);

    $component->assertSee('Create a poll');

    $otherUser = User::factory()->create();
    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $otherUser->id]);

    $component->assertDontSee('Create a poll');
});

test('poll button is not visible for replies', function (): void {
    $user = User::factory()->create();
    $question = Question::factory()->create(['to_id' => $user->id]);

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id, 'parentId' => $question->id]);

    $component->assertDontSee('Create a poll');
});

test('can create a poll with valid options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue', 'Green'])
        ->set('pollDuration', 3)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->whereNotNull('poll_expires_at')
        ->first();

    expect($question)->not->toBeNull();
    expect($question->pollOptions)->toHaveCount(3);
    expect($question->pollOptions->pluck('text')->toArray())->toBe(['Red', 'Blue', 'Green']);
    expect($question->poll_expires_at)->not->toBeNull();
    expect((int) $question->created_at->diffInDays($question->poll_expires_at, false))->toBe(3);
});

test('validates poll requires at least 2 options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', ''])
        ->set('pollDuration', 3)
        ->call('store')
        ->assertHasErrors('pollOptions');
});

test('validates poll cannot have more than 4 options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue', 'Green', 'Yellow', 'Purple'])
        ->set('pollDuration', 3)
        ->call('store')
        ->assertHasErrors(['pollOptions' => 'A poll can have maximum 4 options.']);
});

test('validates poll options are required when poll is enabled', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['', ''])
        ->call('store')
        ->assertHasErrors('pollOptions');
});

test('validates poll option length', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', str_repeat('a', 41)])
        ->call('store')
        ->assertHasErrors('pollOptions');
});

test('creates regular question when poll is disabled', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'This is a regular update')
        ->set('isPoll', false)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->whereNull('poll_expires_at')
        ->first();

    expect($question)->not->toBeNull();
    expect($question->pollOptions)->toHaveCount(0);
});

test('resets poll state after successful submission', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue'])
        ->call('store')
        ->assertSet('isPoll', false)
        ->assertSet('pollOptions', ['', '']);
});

test('trims whitespace from poll options', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['  Red  ', '  Blue  '])
        ->set('pollDuration', 1)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->whereNotNull('poll_expires_at')
        ->first();

    expect($question->pollOptions->pluck('text')->toArray())->toBe(['Red', 'Blue']);
});

test('validates poll duration is required when creating a poll', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue'])
        ->set('pollDuration', 0)
        ->call('store')
        ->assertHasErrors(['pollDuration']);
});

test('validates poll duration maximum value', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue'])
        ->set('pollDuration', 8)
        ->call('store')
        ->assertHasErrors(['pollDuration']);
});

test('stores poll expiration date correctly', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'What is your favorite color?')
        ->set('isPoll', true)
        ->set('pollOptions', ['Red', 'Blue'])
        ->set('pollDuration', 5)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->whereNotNull('poll_expires_at')
        ->first();

    expect($question->poll_expires_at)->not->toBeNull();
    expect((int) $question->created_at->diffInDays($question->poll_expires_at, false))->toBe(5);
});

test('does not set poll expiration for non-poll questions', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class, ['toId' => $user->id])
        ->set('content', 'This is a regular update')
        ->set('isPoll', false)
        ->call('store');

    $question = Question::where('content', '__UPDATE__')
        ->whereNull('poll_expires_at')
        ->first();

    expect($question->poll_expires_at)->toBeNull();
});

test('store with user questions_preference set to public', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $userA->update(['prefers_anonymous_questions' => false]);

    expect(Question::count())->toBe(0);

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

    $question = Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeFalse();
});

test('store with user questions_preference set to anonymously', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $userA->update(['prefers_anonymous_questions' => true]);

    expect(Question::count())->toBe(0);

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

    $question = Question::first();

    expect($question->from_id)->toBe($userA->id)
        ->and($question->to_id)->toBe($userB->id)
        ->and($question->content)->toBe('Hello World')
        ->and($question->anonymously)->toBeTrue();
});

test('anonymous set back to user\'s preference after sending a question', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $userA->update(['prefers_anonymous_questions' => false]);

    expect(Question::count())->toBe(0);

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

    $question = Question::first();

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
    $user = User::factory()->create(['is_verified' => true]);
    $file = UploadedFile::fake()->image('photo1.jpg');
    $date = now()->format('Y-m-d');
    $path = $file->store("images/{$date}", ['disk' => Create::IMAGE_DISK]);

    $component = Livewire::actingAs($user)->test(Create::class);

    $component->set('images', [$file]);

    $method = new ReflectionMethod(Create::class, 'uploadImages');
    $method->setAccessible(true);
    $method->invoke($component->instance());

    expect(session('images'))->toBeArray()
        ->and(session('images'))->toContain($path);

    $component->assertDispatched('image.uploaded');

    $component->assertSet('images', []);
});

test('unused image cleanup when store is called', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo1.jpg');
    $date = now()->format('Y-m-d');
    $path = $file->store("images/{$date}", ['disk' => Create::IMAGE_DISK]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);
    $component->set('images', [$file]);

    $method = new ReflectionMethod(Create::class, 'uploadImages');
    $method->setAccessible(true);
    $method->invoke($component->instance());

    Storage::disk()->assertExists($path);

    expect(session('images'))->toBeArray()
        ->and(session('images'))->toContain($path);

    $component->set('content', 'Hello World');
    $component->call('store');

    Storage::disk()->assertMissing($path);
    expect(session('images'))->toBeNull();
});

test('used images are NOT cleanup when store is called', function () {
    $user = User::factory()->create(['is_verified' => true]);
    $file = UploadedFile::fake()->image('photo1.jpg');
    $name = $file->hashName();
    $date = now()->format('Y-m-d');
    $path = 'images/'.$date.'/'.$name;

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);
    $component->set('images', [$file]);

    Storage::disk()->assertExists($path);

    expect(session('images'))->toBeArray()
        ->and(session('images'))->toContain($path);

    $url = Storage::disk()->url($path);

    $component->set('content', "![Image Alt Text]({$url})");
    $component->call('store');

    Storage::disk()->assertExists($path);
    expect(session('images'))->toBeNull();
});

test('delete image', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo1.jpg');
    $path = $file->store('images', ['disk' => Create::IMAGE_DISK]);

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    Storage::disk()->assertExists($path);

    $method = new ReflectionMethod(Create::class, 'deleteImage');
    $method->setAccessible(true);
    $method->invoke($component->instance(), $path);

    $pathAgain = $file->store('images', ['disk' => Create::IMAGE_DISK]);
    Storage::disk()->assertExists($pathAgain);

    $method->invoke($component->instance(), $pathAgain);

    Storage::disk()->assertMissing($pathAgain);
});

test('optimizeImage method resizes and saves the image', function () {

    $user = User::factory()->create();
    $testImage = UploadedFile::fake()->image('test.jpg', 1200, 1200); // Larger than 1000x1000

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $method = new ReflectionMethod(Create::class, 'optimizeImage');
    $method->setAccessible(true);
    $path = $method->invoke($component->instance(), $testImage);

    Storage::disk()->assertExists($path);

    $optimizedImagePath = Storage::disk()->path($path);

    $originalImageSize = filesize($testImage->getPathname());
    $optimizedImageSize = filesize($optimizedImagePath);

    expect($optimizedImageSize)->toBeLessThan($originalImageSize);

    $manager = ImageManager::imagick();
    $image = $manager->read($optimizedImagePath);

    expect($image->width())->toBeLessThanOrEqual(1000)
        ->and($image->height())->toBeLessThanOrEqual(1000);
});

test('it skips the optimization for gif', function () {

    $user = User::factory()->create();

    $testImage = UploadedFile::fake()->image('test.gif', 1200, 1200); // Larger than 1000x1000

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $method = new ReflectionMethod(Create::class, 'optimizeImage');
    $method->setAccessible(true);
    $path = $method->invoke($component->instance(), $testImage);

    Storage::disk()->assertExists($path);

    // cross check the image
    $optimizedImagePath = Storage::disk()->path($path);
    $originalImageSize = filesize($testImage->getPathname());
    $optimizedImageSize = filesize($optimizedImagePath);
    expect($optimizedImageSize)->toBe($originalImageSize);

    $manager = ImageManager::imagick();
    $image = $manager->read($optimizedImagePath);
    expect($image->width())->toBe(1200)
        ->and($image->height())->toBe(1200);
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

    $method = new ReflectionMethod(Create::class, 'uploadImages');
    $method->setAccessible(true);
    $method->invoke($component->instance());

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

    $method = new ReflectionMethod(Create::class, 'uploadImages');
    $method->setAccessible(true);
    $method->invoke($component->instance());

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

test('only verified users can upload images', function () {
    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('images', [UploadedFile::fake()->image('test.jpg')]);
    $component->call('runImageValidation');

    $component->assertRedirect(route('verification.notice'));
});

test('only verified users can create questions', function () {
    $user = User::factory()->unverified()->create();

    $component = Livewire::actingAs($user)->test(Create::class, [
        'toId' => $user->id,
    ]);

    $component->set('content', 'Hello World');
    $component->call('store');

    $component->assertRedirect(route('verification.notice'));
});
