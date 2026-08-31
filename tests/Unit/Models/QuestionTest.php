<?php

declare(strict_types=1);

use App\Models\Hashtag;
use App\Models\Like;
use App\Models\PollOption;
use App\Models\Question;
use App\Models\User;

test('to array', function (): void {
    $question = Question::factory()->create()->fresh();

    expect(array_keys($question->toArray()))->toContain(
        'id',
        'from_id',
        'to_id',
        'content',
        'answer',
        'answer_created_at',
        'anonymously',
        'is_reported',
        'created_at',
        'updated_at',
        'pinned',
        'is_ignored',
        'views',
        'answer_updated_at',
        'parent_id',
        'root_id',
        'poll_expires_at',
    )->toHaveCount(17);
});

test('content', function (): void {
    Http::fake([
        '*' => Http::response('', 404),
    ]);

    $question = Question::factory()->create([
        'content' => 'Hello https://example.com, how are you? https://example.com',
    ])->fresh();

    expect($question->content)->toBe('Hello <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>, how are you? <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>');
});

test('relations', function (): void {
    $question = Question::factory()
        ->hasDescendants(2)
        ->hasChildren(2)
        ->hasHashtags(1)
        ->create(['poll_expires_at' => now()->addDays(1)]);

    $question->likes()->saveMany(Like::factory()->count(3)->make());
    $question->pollOptions()->saveMany(PollOption::factory()->count(2)->make());

    $child = $question->children()->with('parent')->first();

    $descendant = $question->descendants()->with('root')->first();

    expect($question->from)->toBeInstanceOf(User::class)
        ->and($question->to)->toBeInstanceOf(User::class)
        ->and($question->likes)->toContainOnlyInstancesOf(Like::class)
        ->and($question->hashtags)->toContainOnlyInstancesOf(Hashtag::class)
        ->and($question->children)->toContainOnlyInstancesOf(Question::class)
        ->and($child->parent)->toBeInstanceOf(Question::class)
        ->and($descendant->root)->toBeInstanceOf(Question::class)
        ->and($question->descendants)->toContainOnlyInstancesOf(Question::class)
        ->and($question->pollOptions)->toContainOnlyInstancesOf(PollOption::class)
        ->and($question->pollOptions)->toHaveCount(2);
});

test('mentions', function (): void {
    User::factory()->create(['username' => 'firstuser']);
    User::factory()->create(['username' => 'seconduser']);

    $question = Question::factory()->create([
        'content' => 'Hello @firstuser! How are you doing?',
        'answer' => 'I am doing fine, @seconduser! @invaliduser is not doing well.',
    ]);

    expect($question->mentions()->count())->toBe(2)
        ->and($question->mentions()->first()->username)->toBe('firstuser')
        ->and($question->mentions()->last()->username)->toBe('seconduser');
});

test('likers relationship returns users who liked the question', function (): void {
    $question = Question::factory()->create();
    $users = User::factory()->count(2)->create();

    foreach ($users as $user) {
        $question->likes()->create(['user_id' => $user->id]);
    }

    expect($question->likers)->toHaveCount(2)
        ->and($question->likers->pluck('id')->sort()->values())->toEqual($users->pluck('id')->sort()->values());
});

test('mentions when there is no answer', function (): void {
    User::factory()->create(['username' => 'firstuser']);
    User::factory()->create(['username' => 'seconduser']);

    $question = Question::factory()->create([
        'content' => 'Hello @firstuser! How are you doing?',
        'answer' => null,
    ]);

    expect($question->mentions()->count())->toBe(0);
});

test('increment views', function (): void {
    $question = Question::factory()->create([
        'answer' => 'Hello',
        'views' => 0,
    ]);

    Question::incrementViews([$question->id]);

    expect($question->fresh()->views)->toBe(1);
});

test('does not increment views without answer', function (): void {
    $question = Question::factory()->create([
        'answer' => null,
        'views' => 0,
    ]);

    Question::incrementViews([$question->id]);

    expect($question->fresh()->views)->toBe(0);
});

test('get sharable answer attribute', function (): void {
    $question = Question::factory()->create([
        'answer' => <<<'text_WRAP'
        Hello
        ```php
        echo "Hello, World!";
        ```
        Answer text
        https://pinkary.com
        text_WRAP,
    ]);

    expect($question->sharable_answer)->toBe('Hello  [👀 see the code on Pinkary 👀]  Answer text pinkary.com');
});

test('get sharable content attribute', function (): void {
    $question = Question::factory()->create([
        'content' => <<<'text_WRAP'
        Hello
        ```php
            echo "Hello, World!";
        ```
        Content text
        https://pinkary.com
        text_WRAP,
    ]);

    expect($question->sharable_content)->toBe('Hello  [👀 see the code on Pinkary 👀]  Content text pinkary.com');
});

test('get sharable text', function (): void {
    $question = new Question();

    expect($question->getSharableText(null))->toBeNull()
        ->and($question->getSharableText('Hello'))->toBe('Hello')
        ->and($question->getSharableText('Hello <div id="link-preview-card">Preview</div>'))->toBe('Hello ')
        ->and($question->getSharableText('Hello <pre><code>Code</code></pre>'))->toBe('Hello  [👀 see the code on Pinkary 👀] ')
        ->and($question->getSharableText('Hello<br>World'))->toBe('Hello World')
        ->and($question->getSharableText('hello<div id="link-preview-card">Preview</div>'))->toBe('hello');
});
