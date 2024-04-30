<?php

declare(strict_types=1);

use App\Livewire\Comments\Delete;
use App\Models\Comment;
use App\Models\User;
use Livewire\Attributes\On;

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
    $this->assertFalse($component->get('isOpen'));
    $this->assertSame($this->comment->id, $component->get('commentId'));
});

test('open modal', function () {
    $component = Livewire::actingAs($this->user)
        ->test(Delete::class, [
            'commentId' => $this->comment->id,
        ]);
    $component->call('openModal', $this->comment->id)
        ->assertSet('isOpen', true);
});

test('open modal auth', function () {
    $component = Livewire::test(Delete::class, [
        'commentId' => $this->comment->id,
    ]);
    $component->call('openModal', $this->comment->id)
        ->assertStatus(403);
});

test('refresh', function () {
    $component = Livewire::actingAs($this->user)
        ->test(Delete::class, [
            'commentId' => $this->comment->id,
        ]);
    $component->set('isOpen', true)
        ->call('refresh')
        ->assertSet('isOpen', false);
    $this->assertNull($component->get('commentId'));
});

test('delete', function () {
    $component = Livewire::actingAs($this->user)
        ->test(Delete::class, [
            'commentId' => $this->comment->id,
        ]);
    $component->set('isOpen', true)
        ->call('delete')
        ->assertDispatched('notification.created')
        ->assertDispatched('comment.deleted')
        ->assertSet('isOpen', false);
    $this->assertNull($component->get('commentId'));
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

test('events', function () {
    $component = Livewire::test(Delete::class, [
        'commentId' => $this->comment->id,
    ]);
    collect($component->invade()->getAttributes())
        ->filter(fn ($attribute) => $attribute instanceof On)
        ->each(function ($attribute) {
            if ($attribute->getName() === 'openModal') {
                $this->assertSame('comment.delete', $attribute->event);
            }
        });
});
