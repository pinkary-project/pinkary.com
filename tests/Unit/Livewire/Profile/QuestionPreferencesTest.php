<?php

declare(strict_types=1);

use App\Livewire\Profile\QuestionPreferences;
use App\Models\User;
use Livewire\Livewire;

test('question preferences appear in profile settings', function (): void {
    $user = User::factory()->create(['question_preference' => 'following']);

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertOk()->assertSeeLivewire(QuestionPreferences::class)
        ->assertSee('Who can ask you questions?');

    Livewire::actingAs($user)->test(QuestionPreferences::class)
        ->assertSet('questionPreference', 'following')
        ->assertSee('Only people you follow can ask you a question.');
});

test('question preferences save only the signed in users selection', function (string $preference): void {
    $user = User::factory()->create(['settings' => ['link_shape' => 'rounded-full']]);
    $other = User::factory()->create();

    Livewire::actingAs($user)->test(QuestionPreferences::class)
        ->call('edit')->assertDispatched('open-modal', 'question-preferences')
        ->set('questionPreference', $preference)->call('update')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', 'question-preferences')
        ->assertDispatched('question-preference.updated')
        ->assertDispatched('notification.created', message: 'Question preference updated.');

    expect($user->refresh()->question_preference->value)->toBe($preference)
        ->and($user->settings)->toBe(['link_shape' => 'rounded-full'])
        ->and($other->refresh()->question_preference->value)->toBe('everyone');
})->with(['everyone', 'following', 'no_one']);

test('question preferences reject invalid selections', function (string $preference): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(QuestionPreferences::class)
        ->set('questionPreference', $preference)->call('update')
        ->assertHasErrors('questionPreference')
        ->assertNotDispatched('question-preference.updated');

    expect($user->refresh()->question_preference->value)->toBe('everyone');
})->with(['', 'followers', 'invalid']);

test('reopening question preferences discards unsaved changes and errors', function (): void {
    $user = User::factory()->create(['question_preference' => 'following']);

    Livewire::actingAs($user)->test(QuestionPreferences::class)
        ->set('questionPreference', 'invalid')->call('update')
        ->assertHasErrors('questionPreference')
        ->call('edit')->assertHasNoErrors()
        ->assertSet('questionPreference', 'following');

    expect($user->refresh()->question_preference->value)->toBe('following');
});
