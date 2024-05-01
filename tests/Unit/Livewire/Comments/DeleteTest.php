<?php

declare(strict_types=1);

use App\Livewire\Comments\Delete;
use App\Models\Comment;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->comment = Comment::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

test('properties', function () {
    $component = Livewire::actingAs($this->user)
        ->test(Delete::class, [
            'commentId' => $this->comment->id,
        ]);
    $this->assertSame($this->comment->id, $component->get('commentId'));
});

test('refresh', function () {
    $component = Livewire::actingAs($this->user)
        ->test(Delete::class, [
            'commentId' => $this->comment->id,
        ])->call('refresh')
        ->assertSet('isOpen', false);
    $this->assertSame('', $component->get('commentId'));
});

test('delete', function () {
    Livewire::actingAs($this->user)
        ->test(Delete::class, [
            'commentId' => $this->comment->id,
        ])
        ->call('delete')
        ->assertDispatched('notification.created')
        ->assertDispatched('refresh.comments');
    $this->assertNull(Comment::find($this->comment->id));
});

test('delete auth', function () {
    $component = Livewire::test(Delete::class, [
        'commentId' => $this->comment->id,
    ]);
    $component->call('delete')
        ->assertStatus(403);
});

test('render', function () {
    $component = Livewire::test(Delete::class, [
        'commentId' => $this->comment->id,
    ]);
    $component->assertSeeLivewire('comments.delete');
});
