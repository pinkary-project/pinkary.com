<?php

declare(strict_types=1);

use App\Livewire\Comments\Show;
use App\Models\Comment;
use App\Models\User;
use Livewire\Attributes\On;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->comment = Comment::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->component = Livewire::actingAs($this->user)
        ->test(Show::class, [
            'commentId' => $this->comment->id,
        ]);
});

test('render', function () {
    $this->component->assertSeeLivewire('comments.show')
        ->assertSee($this->comment->content);
});

test('refresh', function () {
    $this->component->call('refresh')
        ->assertStatus(200);
});

test('events', function () {
    collect(($this->component->invade()->getAttributes())
        ->filter(fn ($attribute) => $attribute instanceof On))
        ->each(function ($attribute) {
            if ($attribute->getName() === 'refresh') {
                $this->assertEquals('comment.updated.{commentId}', $attribute->event);
            }
        });
});
