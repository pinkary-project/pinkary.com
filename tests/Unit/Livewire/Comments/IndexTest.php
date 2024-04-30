<?php

declare(strict_types=1);

use App\Livewire\Comments\Index;
use App\Models\Comment;
use App\Models\Question;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->question = Question::factory()->create();
    $this->comment = Comment::factory()->create([
        'question_id' => $this->question->id,
        'user_id' => $this->user->id,
    ]);
    $this->component = Livewire::actingAs($this->user)
        ->test(Index::class, [
            'questionId' => $this->question->id,
        ]);
});

test('refresh events', function () {
    collect($this->component->instance()->getAttributes())
        ->filter(fn ($attribute) => $attribute instanceof On)
        ->each(function ($attribute) {
            if ($attribute->getName() === 'refresh') {
                $this->assertContains('comment.created', $attribute->event);
                $this->assertContains('comment.deleted', $attribute->event);
                $this->assertContains('comment.updated', $attribute->event);
            }
        });
});

test('refresh', function () {
    $this->component->call('refresh')
        ->assertStatus(200);
});

test('render', function () {
    $this->component->assertSee($this->comment->content);
});

test('do not render reported comments', function () {
    $comment = Comment::factory()->create([
        'question_id' => $this->question->id,
        'is_reported' => true,
    ]);
    $this->component->assertDontSee($comment->content);
});

test('load more', function () {
    $comments = Comment::factory(20)->create([
        'question_id' => $this->question->id,
    ]);
    $this->component->assertDontSee($comments->first()->content);
    $this->component->call('loadMore');
    $this->component->assertSee($comments->first()->content);
});
