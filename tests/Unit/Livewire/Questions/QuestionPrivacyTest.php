<?php

declare(strict_types=1);

use App\Livewire\Questions\Create;
use App\Models\Question;
use App\Models\User;
use Livewire\Livewire;

test('restricted visitors do not see a question composer', function (string $preference, string $message): void {
    $sender = User::factory()->create();
    $recipient = User::factory()->create(['question_preference' => $preference]);

    Livewire::actingAs($sender)->test(Create::class, ['toId' => $recipient->id])
        ->assertOk()->assertSee($message)
        ->assertDontSeeHtml('data-post-composer')
        ->set('content', 'Hello!')->call('store')->assertForbidden();

    $this->assertDatabaseCount('questions', 0);
    $this->assertDatabaseCount('notifications', 0);
})->with([
    ['following', "This user isn't accepting questions from you right now."],
    ['no_one', "This user isn't accepting questions right now."],
]);

test('restricted guests do not see a question composer', function (string $preference): void {
    $recipient = User::factory()->create(['question_preference' => $preference]);

    Livewire::test(Create::class, ['toId' => $recipient->id])
        ->assertOk()->assertDontSeeHtml('data-post-composer');
})->with(['following', 'no_one']);

test('allowed visitors can still ask anonymous and named questions', function (bool $anonymously): void {
    $sender = User::factory()->create();
    $recipient = User::factory()->create(['question_preference' => 'following']);
    $recipient->following()->attach($sender);

    Livewire::actingAs($sender)->test(Create::class, ['toId' => $recipient->id])
        ->assertSeeHtml('data-post-composer')
        ->set('anonymously', $anonymously)->set('content', 'Hello!')->call('store')
        ->assertHasNoErrors()->assertDispatched('question.created');

    expect(Question::sole()->anonymously)->toBe($anonymously);
})->with([true, false]);

test('an open composer rechecks privacy and following on submission', function (bool $unfollow): void {
    $sender = User::factory()->create();
    $recipient = User::factory()->create(['question_preference' => 'following']);
    $recipient->following()->attach($sender);
    $component = Livewire::actingAs($sender)->test(Create::class, ['toId' => $recipient->id])->set('content', 'Hello!');

    if ($unfollow) {
        $recipient->following()->detach($sender);
    } else {
        $recipient->update(['question_preference' => 'no_one']);
    }

    $component->call('store')->assertForbidden();
    $this->assertDatabaseCount('questions', 0);
})->with([true, false]);

test('turning questions off does not prevent posts or comments', function (bool $comment): void {
    $sender = User::factory()->create(['question_preference' => 'no_one']);
    $recipient = User::factory()->create(['question_preference' => 'no_one']);
    $post = Question::factory()->create(['to_id' => $recipient->id, 'from_id' => $recipient->id, 'answer' => 'Existing post']);

    Livewire::actingAs($sender)->test(Create::class, [
        'toId' => $sender->id,
        'parentId' => $comment ? $post->id : null,
    ])->assertSeeHtml('data-post-composer')
        ->set('content', 'Hello!')->call('store')->assertHasNoErrors();

    $this->assertDatabaseCount('questions', 2);
    expect($post->refresh()->answer)->toBe('Existing post');
})->with([true, false]);
