<?php

declare(strict_types=1);

use App\Http\Middleware\BlockBots;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

test('allows normal user agents', function (): void {
    Route::get('/test-block-bots', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $this->get('/test-block-bots', ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
        ->assertOk();
});

test('allows Googlebot through (search indexing)', function (): void {
    Route::get('/test-block-bots', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $this->get('/test-block-bots', ['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
        ->assertOk();
});

test('blocks GPTBot with a 403 response', function (): void {
    Route::get('/test-block-bots', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $this->get('/test-block-bots', ['User-Agent' => 'Mozilla/5.0 (compatible; GPTBot/1.0; +https://openai.com/bot)'])
        ->assertStatus(403)
        ->assertSee('Access Denied');
});

test('blocks ClaudeBot with a 403 response', function (): void {
    Route::get('/test-block-bots', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $this->get('/test-block-bots', ['User-Agent' => 'ClaudeBot/1.0 (+https://claude.ai/bot)'])
        ->assertStatus(403);
});

test('blocks PerplexityBot with a 403 response', function (): void {
    Route::get('/test-block-bots', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $this->get('/test-block-bots', ['User-Agent' => 'PerplexityBot/1.0 (+https://perplexity.ai/bot)'])
        ->assertStatus(403);
});

test('blocks Google-Extended with a 403 response', function (): void {
    Route::get('/test-block-bots', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $this->get('/test-block-bots', ['User-Agent' => 'Mozilla/5.0 (compatible; Google-Extended; +https://developers.google.com/search/apis/ads/robot)'])
        ->assertStatus(403);
});

test('blocks HeadlessChrome with a 403 response', function (): void {
    Route::get('/test-block-bots', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $this->get('/test-block-bots', ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/120.0.0.0 Safari/537.36'])
        ->assertStatus(403);
});

test('matching is case-insensitive', function (): void {
    Route::get('/test-block-bots', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $this->get('/test-block-bots', ['User-Agent' => 'mozilla/5.0 (compatible; gptbot/1.0)'])
        ->assertStatus(403);

    $this->get('/test-block-bots', ['User-Agent' => 'Mozilla/5.0 headlesschrome/120.0.0.0 Safari/537.36'])
        ->assertStatus(403);
});

test('blocks empty user agent with a 403 response', function (): void {
    Route::get('/test-block-bots', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $this->get('/test-block-bots', ['User-Agent' => ''])
        ->assertStatus(403);
});

test('skips non-GET requests', function (): void {
    Route::post('/test-block-bots-post', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $this->post('/test-block-bots-post', [], ['User-Agent' => 'GPTBot/1.0'])
        ->assertOk();
});

test('does not block authenticated users', function (): void {
    Route::get('/test-block-bots', fn (): Response => response(status: 200))
        ->middleware(BlockBots::class);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/test-block-bots', ['User-Agent' => 'GPTBot/1.0'])
        ->assertOk();
});

test('block.bots middleware is applied to public GET routes', function (): void {
    $this->get('/about', ['User-Agent' => 'GPTBot/1.0'])
        ->assertStatus(403);
});

test('block.bots middleware is not applied to login', function (): void {
    $this->get('/login', ['User-Agent' => 'GPTBot/1.0'])
        ->assertOk();
});

test('block.bots middleware is not applied to authenticated routes', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/profile', ['User-Agent' => 'GPTBot/1.0'])
        ->assertOk();
});

test('block.bots middleware is not applied to POST routes', function (): void {
    $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ], ['User-Agent' => 'GPTBot/1.0'])
        ->assertSessionHasErrors('email');
});
