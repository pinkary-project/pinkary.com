<?php

declare(strict_types=1);

use App\Jobs\UpdateUserAvatar;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    config([
        'app.url' => 'https://pinkary.test',
        'services.turnstile.key' => 'test-site-key',
        'services.turnstile.secret' => 'test-secret',
        'services.turnstile.hostname' => 'pinkary.test',
    ]);
    Http::preventStrayRequests();
});

/** @return array<string, mixed> */
function captchaRegistration(): array
{
    return [
        'name' => 'Pinkary User', 'username' => 'pinkaryuser', 'email' => 'pinkary@example.com',
        'password' => 'password', 'password_confirmation' => 'password', 'terms' => true,
        'cf-turnstile-response' => 'test-token', 'captcha_state' => '019a6f52-13ef-7000-9000-000000000001',
    ];
}

test('registration requires captcha and exposes no secret in metadata', function (): void {
    $this->getJson(route('api.v1.captcha.show', ['action' => 'register']))
        ->assertOk()->assertJsonPath('data.required', true)
        ->assertJsonPath('data.widget_path', '/api/v1/captcha/widget')
        ->assertDontSee('test-secret');

    $this->postJson(route('api.v1.auth.register'), array_diff_key(captchaRegistration(), array_flip(['cf-turnstile-response', 'captcha_state'])))
        ->assertUnprocessable()->assertJsonValidationErrors(['cf-turnstile-response', 'captcha_state']);

    $this->assertDatabaseCount('users', 0);
    Http::assertNothingSent();
});

test('the hosted widget uses the API origin and a fragment-only result relay', function (): void {
    $this->get(route('api.v1.captcha.widget', ['action' => 'register', 'state' => captchaRegistration()['captcha_state']]))
        ->assertOk()->assertHeader('content-type', 'text/html; charset=UTF-8')
        ->assertSee('test-site-key', false)->assertSee('destination.hash', false)
        ->assertDontSee('test-secret');

    $this->get(route('api.v1.captcha.result', ['token' => 'must-not-reflect', 'status' => '<script>']))
        ->assertOk()->assertDontSee('must-not-reflect')->assertDontSee('<script>', false);
});

test('widget requests return 422 for unsupported actions or invalid state', function (array $query): void {
    $this->getJson(route('api.v1.captcha.widget', $query))->assertUnprocessable();
})->with([
    'unsupported action' => [['action' => 'login', 'state' => '019a6f52-13ef-7000-9000-000000000001']],
    'malformed state' => [['action' => 'register', 'state' => '<script>']],
]);

test('the widget returns 503 when keys are not configured', function (): void {
    config(['services.turnstile.secret' => null]);

    $this->getJson(route('api.v1.captcha.widget', ['action' => 'register', 'state' => captchaRegistration()['captcha_state']]))
        ->assertServiceUnavailable();
});

test('registration returns 422 for rejected or mismatched verification', function (array $verification): void {
    Http::fake(['challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response($verification)]);

    $this->postJson(route('api.v1.auth.register'), captchaRegistration())
        ->assertUnprocessable()->assertJsonValidationErrors('cf-turnstile-response');

    $this->assertDatabaseCount('users', 0);
    Http::assertSentCount(1);
})->with([
    'rejected' => [['success' => false]],
    'nonboolean success' => [['success' => 'true', 'hostname' => 'pinkary.test', 'action' => 'register', 'cdata' => '019a6f52-13ef-7000-9000-000000000001']],
    'wrong hostname' => [['success' => true, 'hostname' => 'evil.test', 'action' => 'register', 'cdata' => '019a6f52-13ef-7000-9000-000000000001']],
    'wrong action' => [['success' => true, 'hostname' => 'pinkary.test', 'action' => 'post', 'cdata' => '019a6f52-13ef-7000-9000-000000000001']],
    'wrong state' => [['success' => true, 'hostname' => 'pinkary.test', 'action' => 'register', 'cdata' => '019a6f52-13ef-7000-9000-000000000002']],
    'missing metadata' => [['success' => true]],
]);

test('registration returns 422 when the verifier is unavailable', function (): void {
    Http::fake(['challenges.cloudflare.com/turnstile/v0/siteverify' => Http::failedConnection()]);

    $this->postJson(route('api.v1.auth.register'), captchaRegistration())
        ->assertUnprocessable()->assertJsonValidationErrors('cf-turnstile-response');

    $this->assertDatabaseCount('users', 0);
});

test('registration returns 422 for malformed verification payloads', function (): void {
    Http::fake(['challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response('not-json')]);

    $this->postJson(route('api.v1.auth.register'), captchaRegistration())
        ->assertUnprocessable()->assertJsonValidationErrors('cf-turnstile-response');

    $this->assertDatabaseCount('users', 0);
});

test('registration validates token bounds and state before contacting Cloudflare', function (array $input): void {
    $this->postJson(route('api.v1.auth.register'), [...captchaRegistration(), ...$input])
        ->assertUnprocessable();

    $this->assertDatabaseCount('users', 0);
    Http::assertNothingSent();
})->with([
    'oversized token' => [['cf-turnstile-response' => str_repeat('a', 2049)]],
    'invalid state' => [['captcha_state' => 'invalid']],
    'array state' => [['captcha_state' => ['invalid']]],
    'array token' => [['cf-turnstile-response' => ['invalid']]],
]);

test('registration verifies the token and falls back to the application hostname', function (): void {
    Queue::fake([UpdateUserAvatar::class]);
    config(['services.turnstile.hostname' => null]);
    Http::fake(['challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
        'success' => true, 'hostname' => 'pinkary.test', 'action' => 'register',
        'cdata' => captchaRegistration()['captcha_state'],
    ])]);

    $this->postJson(route('api.v1.auth.register'), captchaRegistration())->assertCreated();

    $this->assertDatabaseHas('users', ['username' => 'pinkaryuser']);
    Queue::assertPushed(UpdateUserAvatar::class);
    Http::assertSent(fn (Request $request): bool => $request['secret'] === 'test-secret' && $request['response'] === 'test-token');
});

test('posting without followers requires captcha in production', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.captcha.show', ['action' => 'post']), $headers)
        ->assertOk()->assertJsonPath('data.required', true);
    $this->postJson(route('api.v1.questions.store'), ['content' => 'A protected update.'], $headers)
        ->assertUnprocessable()->assertJsonValidationErrors('cf-turnstile-response');

    $this->assertDatabaseCount('questions', 0);
});

test('users with followers can post without captcha in production', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    $user = User::factory()->create();
    $user->followers()->attach(User::factory()->create()->id);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.captcha.show', ['action' => 'post']), $headers)
        ->assertOk()->assertJsonPath('data.required', false);
    $this->postJson(route('api.v1.questions.store'), ['content' => 'An established update.'], $headers)->assertCreated();

    $this->assertDatabaseCount('questions', 1);
    Http::assertNothingSent();
});

test('a valid post challenge publishes a thread in production', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    $user = User::factory()->create();
    Http::fake(['challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
        'success' => true, 'hostname' => 'pinkary.test', 'action' => 'post', 'cdata' => captchaRegistration()['captcha_state'],
    ])]);

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Protected thread.', 'thread_posts' => ['Follow-up.'],
        'cf-turnstile-response' => 'post-token', 'captcha_state' => captchaRegistration()['captcha_state'],
    ], ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken])->assertCreated();

    $this->assertDatabaseCount('questions', 2);
    Http::assertSent(fn (Request $request): bool => $request['response'] === 'post-token');
});

test('commenting without followers requires a separate comment challenge in production', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    $user = User::factory()->create();
    $question = Question::factory()->create(['answer' => 'Public answer.', 'answer_created_at' => now()]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.questions.comments.store', $question), ['content' => 'Protected reply.'], $headers)
        ->assertUnprocessable()->assertJsonValidationErrors('cf-turnstile-response');

    $this->assertDatabaseCount('questions', 1);
    Http::assertNothingSent();
});

test('a comment challenge publishes a protected reply in production', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    $user = User::factory()->create();
    $question = Question::factory()->create(['answer' => 'Public answer.', 'answer_created_at' => now()]);
    Http::fake(['challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
        'success' => true, 'hostname' => 'pinkary.test', 'action' => 'comment',
        'cdata' => captchaRegistration()['captcha_state'],
    ])]);

    $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'Protected reply.', 'cf-turnstile-response' => 'comment-token',
        'captcha_state' => captchaRegistration()['captcha_state'],
    ], ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken])->assertCreated();

    $this->assertDatabaseHas('questions', ['parent_id' => $question->id, 'from_id' => $user->id, 'answer' => 'Protected reply.']);
    Http::assertSent(fn (Request $request): bool => $request['response'] === 'comment-token');
});

test('a post challenge cannot be reused to publish a comment in production', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    $user = User::factory()->create();
    $question = Question::factory()->create(['answer' => 'Public answer.', 'answer_created_at' => now()]);
    Http::fake(['challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
        'success' => true, 'hostname' => 'pinkary.test', 'action' => 'post',
        'cdata' => captchaRegistration()['captcha_state'],
    ])]);

    $this->postJson(route('api.v1.questions.comments.store', $question), [
        'content' => 'Protected reply.', 'cf-turnstile-response' => 'post-token',
        'captcha_state' => captchaRegistration()['captcha_state'],
    ], ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken])
        ->assertUnprocessable()->assertJsonValidationErrors('cf-turnstile-response');

    $this->assertDatabaseCount('questions', 1);
    Http::assertSentCount(1);
});

test('registration fails closed without a configured secret and does not contact Cloudflare', function (): void {
    config(['services.turnstile.secret' => null]);

    $this->postJson(route('api.v1.auth.register'), captchaRegistration())
        ->assertUnprocessable()->assertJsonValidationErrors(['cf-turnstile-response' => 'Please complete the security check and try again.']);

    $this->assertDatabaseCount('users', 0);
    Http::assertNothingSent();
});

test('local development does not require captcha', function (): void {
    app()->detectEnvironment(fn (): string => 'local');

    $this->getJson(route('api.v1.captcha.show', ['action' => 'register']))->assertOk()->assertJsonPath('data.required', false);
});
