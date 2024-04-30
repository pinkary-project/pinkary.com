<?php

declare(strict_types=1);

use App\Livewire\Comments\Edit;
use App\Models\Comment;
use App\Models\User;
use App\Rules\NoBlankCharacters;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->comment = Comment::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

test('properties', function () {
    $component = Livewire::test(Edit::class, [
        'commentId' => $this->comment->id,
    ]);
    $this->assertNull($component->get('content'));
    $this->assertSame($this->comment->id, $component->get('commentId'));
    $this->assertFalse($component->get('isOpen'));
});

test('openModal', function () {
    Livewire::actingAs($this->user)
        ->test(Edit::class)
        ->call('openModal', $this->comment->id)
        ->assertSet('content', $this->comment->raw_content)
        ->assertSet('isOpen', true);
});

test('openModal auth', function () {
    Livewire::test(Edit::class)
        ->call('openModal', $this->comment->id)
        ->assertStatus(403);
});

test('openModal events', function () {
    $component = Livewire::test(Edit::class);
    collect($component->invade()->getAttributes())
        ->filter(fn ($attribute) => $attribute instanceof On)
        ->each(function ($attribute) {
            if ($attribute->getName() === 'openModal') {
                $this->assertEquals('comment.edit', $attribute->event);
            }
        });
});

test('refresh', function () {
    Livewire::actingAs($this->user)
        ->test(Edit::class, [
            'commentId' => $this->comment->id,
        ])
        ->set('isOpen', true)
        ->set('content', 'New content')
        ->call('refresh')
        ->assertSet('content', '')
        ->assertSet('isOpen', false);
});

test('update', function () {
    Livewire::actingAs($this->user)
        ->test(Edit::class, [
            'commentId' => $this->comment->id,
        ])
        ->set('isOpen', true)
        ->set('content', 'New content')
        ->call('update')
        ->assertDispatched('notification.created')
        ->assertDispatched('comment.updated')
        ->assertSet('content', '')
        ->assertSet('isOpen', false);

    $this->assertEquals(
        'New content',
        $this->comment->refresh()->content
    );
});

test('content validation rules', function () {
    $component = Livewire::test(Edit::class);
    collect($component->instance()->getAttributes())
        ->filter(fn ($attribute) => $attribute instanceof Validate)
        ->each(function ($attribute) {
            if ($attribute->getName() === 'content') {
                $this->assertEquals([
                    'required',
                    'string',
                    'max:255',
                    'min:5',
                    new NoBlankCharacters,
                ], $attribute->rule);
            }
        });
});

test('does not update if same content', function () {
    Livewire::actingAs($this->user)
        ->test(Edit::class, [
            'commentId' => $this->comment->id,
        ])
        ->set('isOpen', true)
        ->set('content', $this->comment->raw_content)
        ->call('update')
        ->assertNotDispatched('notification.created')
        ->assertNotDispatched('comment.updated');
});

test('update auth', function () {
    Livewire::test(Edit::class, [
        'commentId' => $this->comment->id,
    ])
        ->call('update')
        ->assertStatus(403);
});
