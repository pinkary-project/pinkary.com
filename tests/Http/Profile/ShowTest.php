<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\PeopleToFollow;
use App\Livewire\Questions\Create;
use App\Models\Question;
use App\Models\User;
use Carbon\CarbonImmutable;
use RyanChandler\LaravelCloudflareTurnstile\Facades\Turnstile;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

test('guest', function (): void {
    $response = $this->get(route('profile.show', ['username' => $this->user->username]));

    $response->assertSee($this->user->name);
});

test('guest does not see captcha or get redirected on the profile composer', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    Turnstile::fake();

    $response = $this->get(route('profile.show', ['username' => $this->user->username]));

    $response->assertOk()
        ->assertSee($this->user->name)
        ->assertSeeLivewire(Create::class)
        ->assertDontSee('cf-turnstile', escape: false);
});

test('auth', function (): void {
    $response = $this->get(route('profile.show', ['username' => $this->user->username]));

    $response->assertSee($this->user->name);
});

it('can show profile on username case-insensitive', function (): void {
    $username = $this->user->username;
    $revertCasingUsername = mb_strtolower((string) $username) ^ mb_strtoupper((string) $username) ^ $username;
    $response = $this->get(route('profile.show', ['username' => $revertCasingUsername]));

    $response->assertSee($this->user->name);
});

it('does increment views', function (): void {
    Queue::fake(IncrementViews::class);
    $this->actingAs($this->user);

    $response = $this->get(route('profile.show', ['username' => $this->user->username]));

    $response->assertSee($this->user->name);
    Queue::assertPushed(IncrementViews::class);
});

it('shows recent interacted users in the people to follow rail', function (): void {
    $interactedUser = User::factory()->create(['name' => 'Profile Rail Interaction']);

    Question::factory()->create([
        'from_id' => $interactedUser->id,
        'to_id' => $this->user->id,
        'answer' => 'Profile answer',
        'updated_at' => now(),
    ]);

    $response = $this->get(route('profile.show', ['username' => $this->user->username]));

    $response->assertOk()
        ->assertSeeLivewire(PeopleToFollow::class)
        ->assertSee('Profile Rail Interaction');
});

it('does not redirect profiles before the sunset date', function (): void {
    $this->travelTo(CarbonImmutable::parse('2026-11-30 23:59:59'));

    $this->user->update(['redirect_url' => 'https://alice.dev']);

    $this->get(route('profile.show', ['username' => $this->user->username]))
        ->assertOk()
        ->assertSee($this->user->name);
});

it('activates profile redirects on december first 2026', function (): void {
    $this->user->update(['redirect_url' => 'https://alice.dev']);

    $this->travelTo(CarbonImmutable::parse('2026-11-30 23:59:59'));

    $this->get(route('profile.show', ['username' => $this->user->username]))
        ->assertOk();

    $this->travelTo(CarbonImmutable::parse('2026-12-01 00:00:00'));

    $this->get(route('profile.show', ['username' => $this->user->username]))
        ->assertStatus(301)
        ->assertRedirect('https://alice.dev');
});

it('permanently redirects profiles after the sunset date', function (): void {
    $this->travelTo(CarbonImmutable::parse('2026-12-01 00:00:01'));

    $this->user->update(['redirect_url' => 'https://alice.dev']);

    $this->get(route('profile.show', ['username' => $this->user->username]))
        ->assertStatus(301)
        ->assertRedirect('https://alice.dev');
});

it('shows the sunset page when the profile has no redirect after the sunset date', function (): void {
    $this->travelTo(CarbonImmutable::parse('2026-12-02 00:00:00'));

    $this->get(route('profile.show', ['username' => $this->user->username]))
        ->assertGone()
        ->assertSee('Pinkary has shut down')
        ->assertSee('@'.$this->user->username)
        ->assertSee('Pinkary went offline on December 1, 2026.')
        ->assertDontSeeLivewire(Create::class);
});

it('does not redirect questions after the sunset date', function (): void {
    $this->travelTo(CarbonImmutable::parse('2026-12-02 00:00:00'));

    $this->user->update(['redirect_url' => 'https://alice.dev']);

    $question = Question::factory()->create([
        'to_id' => $this->user->id,
        'answer' => 'Still available',
    ]);

    $this->get(route('questions.show', [
        'username' => $this->user->username,
        'question' => $question->id,
    ]))->assertOk()
        ->assertSee('Still available');
});

it('keeps the redirect on the current username after a rename', function (): void {
    $this->travelTo(CarbonImmutable::parse('2026-12-02 00:00:00'));

    $oldUsername = $this->user->username;

    $this->user->update([
        'username' => 'renamed_user',
        'redirect_url' => 'https://alice.dev',
    ]);

    $this->get(route('profile.show', ['username' => 'renamed_user']))
        ->assertStatus(301)
        ->assertRedirect('https://alice.dev');

    $this->get(route('profile.show', ['username' => $oldUsername]))
        ->assertNotFound();
});

it('does not redirect deleted profiles', function (): void {
    $this->travelTo(CarbonImmutable::parse('2026-12-02 00:00:00'));

    $this->user->update(['redirect_url' => 'https://alice.dev']);

    $username = $this->user->username;

    $this->user->purge();

    $this->get(route('profile.show', ['username' => $username]))
        ->assertNotFound();
});
