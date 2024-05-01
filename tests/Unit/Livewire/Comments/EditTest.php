<?php

declare(strict_types=1);

use App\Livewire\Comments\Edit;
use App\Models\Comment;
use App\Models\User;
use App\Rules\NoBlankCharacters;
use Livewire\Attributes\Validate;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->comment = Comment::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->component = Livewire::test(Edit::class, [
        'commentId' => $this->comment->id,
    ]);
});

test('properties', function () {
    $this->assertSame($this->comment->raw_content, $this->component->get('content'));
    $this->assertSame($this->comment->id, $this->component->get('commentId'));
});

test('refresh', function () {
    Livewire::test(Edit::class, [
        'commentId' => $this->comment->id,
    ])
        ->call('refresh')
        ->assertSessionDoesntHaveErrors('content')
        ->assertDispatched('close-modal');
});

test('update', function () {
    Livewire::actingAs($this->user)
        ->test(Edit::class, [
            'commentId' => $this->comment->id,
        ])
        ->set('content', 'New content')
        ->call('update')
        ->assertDispatched('refresh.comments')
        ->assertDispatched("comment.updated.{$this->comment->id}")
        ->assertDispatched('notification.created');
    $this->assertEquals(
        'New content',
        $this->comment->refresh()->content
    );
});

test('content validation rules', function () {
    $component = Livewire::test(Edit::class, [
        'commentId' => $this->comment->id,
    ]);
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
        ->set('content', $this->comment->raw_content)
        ->call('update')
        ->assertNotDispatched('refresh.comments')
        ->assertNotDispatched('comment.updated')
        ->assertNotDispatched('notification.created');
});

test('update auth', function () {
    Livewire::test(Edit::class, [
        'commentId' => $this->comment->id,
    ])
        ->call('update')
        ->assertStatus(403);
});
