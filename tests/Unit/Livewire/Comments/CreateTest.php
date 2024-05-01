<?php

declare(strict_types=1);

use App\Livewire\Comments\Create;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('properties', function () {
    $component = Livewire::test(Create::class, [
        'questionId' => '1',
    ]);
    $this->assertSame('', $component->get('content'));
    $this->assertSame('1', $component->get('questionId'));
});

test('content validation', function () {
    $component = Livewire::test(Create::class, [
        'questionId' => '1',
    ]);
    collect($component->invade()->getAttributes()->all())
        ->each(function ($attribute) {
            if ($attribute instanceof Validate) {
                $this->assertContains('required', $attribute->rule);
                $this->assertContains('string', $attribute->rule);
                $this->assertContains('max:255', $attribute->rule);
                $this->assertContains('min:5', $attribute->rule);
            }
        });
});

test('refresh', function () {
    Livewire::actingAs($this->user)
        ->test(Create::class, [
            'questionId' => '1',
        ])
        ->set('content', 'New content')
        ->call('refresh')
        ->assertSet('content', '')
        ->assertSessionDoesntHaveErrors('content')
        ->assertDispatched('close-modal');
});

test('store', function () {
    Livewire::actingAs($this->user)
        ->test(Create::class, [
            'questionId' => '1',
        ])
        ->set('content', 'New content')
        ->call('store')
        ->assertDispatched('refresh.comments')
        ->assertDispatched('notification.created');
});

test('store auth', function () {
    Livewire::test(Create::class, [
        'questionId' => '1',
    ])
        ->call('store')
        ->assertForbidden();
});
