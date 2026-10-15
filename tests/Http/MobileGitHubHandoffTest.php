<?php

declare(strict_types=1);

use App\Actions\Users\CompleteMobileGitHubHandoff;
use App\Actions\Users\CreateMobileGitHubHandoff;
use App\Actions\Users\FailMobileGitHubHandoff;
use App\Actions\Users\StartMobileGitHubHandoff;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

function createMobileHandoffRecord(
    User $user,
    string $status = 'pending',
    ?CarbonImmutable $expiresAt = null,
    ?string $token = null,
): string {
    $id = (string) Str::uuid();
    $token ??= Str::random(64);
    $now = now();

    DB::table('mobile_github_handoffs')->insert([
        'id' => $id,
        'user_id' => $user->id,
        'token_hash' => hash('sha256', $token),
        'status' => $status,
        'expires_at' => $expiresAt ?? CarbonImmutable::now()->addMinutes(10),
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return $id;
}

function fakeGitHubSponsorshipLookup(): void
{
    Http::fake([
        'api.github.com/graphql' => Http::response(['data' => ['user' => [
            'sponsorshipForViewerAsSponsorable' => [],
        ]]]),
    ]);
}

test('creating a handoff expires previous active handoffs and removes old records', function (): void {
    $user = User::factory()->create();
    $oldRecord = createMobileHandoffRecord($user, 'failed', CarbonImmutable::now()->subDays(2));
    $pending = createMobileHandoffRecord($user);
    $started = createMobileHandoffRecord($user, 'started');
    $completed = createMobileHandoffRecord($user, 'completed');

    $handoff = app(CreateMobileGitHubHandoff::class)->handle($user);

    expect($handoff['id'])->toBeString()
        ->and($handoff['token'])->toBeString()
        ->and($handoff['expires_at']->isFuture())->toBeTrue();

    $this->assertDatabaseMissing('mobile_github_handoffs', ['id' => $oldRecord]);
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $pending, 'status' => 'expired']);
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $started, 'status' => 'expired']);
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $completed, 'status' => 'completed']);
    $this->assertDatabaseHas('mobile_github_handoffs', [
        'id' => $handoff['id'],
        'token_hash' => hash('sha256', $handoff['token']),
        'status' => 'pending',
    ]);
});

test('starting a handoff checks its secret, expiry, and one-time status', function (): void {
    $user = User::factory()->create();
    $handoff = app(CreateMobileGitHubHandoff::class)->handle($user);
    $start = app(StartMobileGitHubHandoff::class);

    expect($start->handle($handoff['id'], 'wrong-secret'))->toBeFalse()
        ->and($start->handle($handoff['id'], $handoff['token']))->toBeTrue()
        ->and($start->handle($handoff['id'], $handoff['token']))->toBeFalse()
        ->and($start->handle((string) Str::uuid(), $handoff['token']))->toBeFalse();

    $expired = createMobileHandoffRecord($user, expiresAt: CarbonImmutable::now()->subSecond());

    expect($start->handle($expired, 'mobile-handoff-token'))->toBeFalse();
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $expired, 'status' => 'expired']);
});

test('the browser handoff starts GitHub OAuth only after redeeming its secret', function (): void {
    $user = User::factory()->create();
    $id = createMobileHandoffRecord($user, token: 'mobile-handoff-token');

    Socialite::shouldReceive('driver->redirect')
        ->andReturn(redirect('https://github.com/login/oauth/authorize'));

    $this->get(route('mobile.github.handoff.start', [
        'handoff' => $id,
        'token' => 'mobile-handoff-token',
    ]))
        ->assertRedirect('https://github.com/login/oauth/authorize')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('Referrer-Policy', 'no-referrer');

    expect(session('mobile.github.handoff_id'))->toBe($id);
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $id, 'status' => 'started']);

    $this->get(route('mobile.github.handoff.start', [
        'handoff' => $id,
        'token' => 'mobile-handoff-token',
    ]))->assertNotFound();
});

test('the OAuth callback completes the account-bound handoff', function (): void {
    fakeGitHubSponsorshipLookup();
    Bus::fake([UpdateUserAvatar::class]);

    $user = User::factory()->create(['is_uploaded_avatar' => false]);
    $id = createMobileHandoffRecord($user, 'started');
    Socialite::shouldReceive('driver->user->getNickname')->andReturn('pinkary-github-user');

    $this->withSession(['mobile.github.handoff_id' => $id])
        ->get(route('profile.connect.github.update'))
        ->assertOk()
        ->assertSeeText('GitHub connected. Return to Pinkary Mobile.')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    expect(session()->has('mobile.github.handoff_id'))->toBeFalse()
        ->and($user->fresh()->github_username)->toBe('pinkary-github-user');

    $this->assertDatabaseHas('mobile_github_handoffs', [
        'id' => $id,
        'status' => 'completed',
        'github_username' => 'pinkary-github-user',
    ]);

    Bus::assertDispatched(UpdateUserAvatar::class);
});

test('cancelling OAuth validates state and fails the handoff', function (): void {
    $user = User::factory()->create();
    $id = createMobileHandoffRecord($user, 'started');

    $this->withSession([
        'mobile.github.handoff_id' => $id,
        'state' => 'expected-state',
    ])->get(route('profile.connect.github.update', [
        'error' => 'access_denied',
        'state' => 'expected-state',
    ]))
        ->assertOk()
        ->assertSeeText('GitHub authorization was cancelled. Return to Pinkary Mobile.');

    expect(session()->has('mobile.github.handoff_id'))->toBeFalse();
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $id, 'status' => 'failed']);
});

test('cancelling OAuth with an invalid state is rejected', function (): void {
    $user = User::factory()->create();
    $id = createMobileHandoffRecord($user, 'started');

    $this->withSession([
        'mobile.github.handoff_id' => $id,
        'state' => 'expected-state',
    ])->get(route('profile.connect.github.update', [
        'error' => 'access_denied',
        'state' => 'wrong-state',
    ]))->assertForbidden();

    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $id, 'status' => 'started']);
});

test('an expired OAuth state can be retried from the mobile app', function (): void {
    $user = User::factory()->create();
    $id = createMobileHandoffRecord($user, 'started');
    Socialite::shouldReceive('driver->user')->andThrow(new InvalidStateException());

    $this->withSession(['mobile.github.handoff_id' => $id])
        ->get(route('profile.connect.github.update'))
        ->assertForbidden()
        ->assertSeeText('The GitHub sign-in session expired. Return to Pinkary Mobile and try again.');

    expect(session('mobile.github.handoff_id'))->toBe($id);
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $id, 'status' => 'started']);
});

test('a GitHub provider failure marks the handoff failed', function (): void {
    $user = User::factory()->create();
    $id = createMobileHandoffRecord($user, 'started');
    Socialite::shouldReceive('driver->user')->andThrow(new RuntimeException('GitHub unavailable'));

    $this->withSession(['mobile.github.handoff_id' => $id])
        ->get(route('profile.connect.github.update'))
        ->assertStatus(502)
        ->assertSeeText('GitHub could not be reached. Return to Pinkary Mobile and try again.');

    expect(session()->has('mobile.github.handoff_id'))->toBeFalse();
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $id, 'status' => 'failed']);
});

test('the callback rejects missing and oversized GitHub usernames', function (): void {
    $user = User::factory()->create();
    $missing = createMobileHandoffRecord($user, 'started');
    Socialite::shouldReceive('driver->user->getNickname')->andReturn(null);

    $this->withSession(['mobile.github.handoff_id' => $missing])
        ->get(route('profile.connect.github.update'))
        ->assertStatus(422)
        ->assertSeeText('GitHub did not provide a usable account name. Return to Pinkary Mobile.');

    $oversized = createMobileHandoffRecord($user, 'started');
    Socialite::shouldReceive('driver->user->getNickname')->andReturn(str_repeat('a', 256));

    $this->withSession(['mobile.github.handoff_id' => $oversized])
        ->get(route('profile.connect.github.update'))
        ->assertStatus(422);

    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $missing, 'status' => 'failed']);
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $oversized, 'status' => 'failed']);
});

test('an ineligible handoff returns a conflict to the browser', function (): void {
    $user = User::factory()->create(['github_username' => 'already-connected']);
    $id = createMobileHandoffRecord($user, 'started');
    Socialite::shouldReceive('driver->user->getNickname')->andReturn('new-github-user');

    $this->withSession(['mobile.github.handoff_id' => $id])
        ->get(route('profile.connect.github.update'))
        ->assertConflict()
        ->assertSeeText('GitHub connection expired or is no longer eligible. Return to Pinkary Mobile.');

    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $id, 'status' => 'failed']);
});

test('failing a handoff only changes started or processing records', function (): void {
    $user = User::factory()->create();
    $started = createMobileHandoffRecord($user, 'started');
    $processing = createMobileHandoffRecord($user, 'processing');
    $pending = createMobileHandoffRecord($user, 'pending');
    $fail = app(FailMobileGitHubHandoff::class);

    $fail->handle($started);
    $fail->handle($processing);
    $fail->handle($pending);

    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $started, 'status' => 'failed']);
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $processing, 'status' => 'failed']);
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $pending, 'status' => 'pending']);
});

test('completion records a GitHub username and queues the provider avatar', function (): void {
    fakeGitHubSponsorshipLookup();
    Bus::fake([UpdateUserAvatar::class]);

    $user = User::factory()->create(['github_username' => null]);
    $id = createMobileHandoffRecord($user, 'started');

    expect(app(CompleteMobileGitHubHandoff::class)->handle($id, 'github-account'))->toBeTrue()
        ->and($user->fresh()->github_username)->toBe('github-account');

    $this->assertDatabaseHas('mobile_github_handoffs', [
        'id' => $id,
        'status' => 'completed',
        'github_username' => 'github-account',
    ]);

    Bus::assertDispatched(UpdateUserAvatar::class);
});

test('completion rejects missing, pending, expired, and no-longer-eligible handoffs', function (): void {
    $user = User::factory()->create(['github_username' => 'already-connected']);
    $pending = createMobileHandoffRecord($user, 'pending');
    $expired = createMobileHandoffRecord($user, 'started', CarbonImmutable::now()->subSecond());
    $connected = createMobileHandoffRecord($user, 'started');
    $processing = createMobileHandoffRecord($user, 'started');

    DB::table('mobile_github_handoffs')->where('id', $processing)->update(['status' => 'processing']);

    $complete = app(CompleteMobileGitHubHandoff::class);

    expect($complete->handle((string) Str::uuid(), 'new-user'))->toBeFalse()
        ->and($complete->handle($pending, 'new-user'))->toBeFalse()
        ->and($complete->handle($expired, 'new-user'))->toBeFalse()
        ->and($complete->handle($connected, 'new-user'))->toBeFalse()
        ->and($complete->handle($processing, 'new-user'))->toBeFalse();

    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $expired, 'status' => 'expired']);
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $connected, 'status' => 'failed']);
});

test('completion rejects a username already connected to another account', function (): void {
    $user = User::factory()->create(['github_username' => null]);
    User::factory()->create(['github_username' => 'taken-github-user']);
    $id = createMobileHandoffRecord($user, 'started');

    expect(app(CompleteMobileGitHubHandoff::class)->handle($id, 'taken-github-user'))->toBeFalse();
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $id, 'status' => 'failed']);
});

test('completion fails if the bound account no longer exists', function (): void {
    $user = User::factory()->create();
    $id = createMobileHandoffRecord($user, 'started');

    Schema::disableForeignKeyConstraints();

    try {
        DB::table('mobile_github_handoffs')->where('id', $id)->update(['user_id' => 999999]);
    } finally {
        Schema::enableForeignKeyConstraints();
    }

    expect(app(CompleteMobileGitHubHandoff::class)->handle($id, 'github-account'))->toBeFalse();
    $this->assertDatabaseHas('mobile_github_handoffs', ['id' => $id, 'status' => 'failed']);
});
