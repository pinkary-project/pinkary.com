<?php

declare(strict_types=1);

use App\Services\Firewall;

beforeEach(function (): void {
    $this->firewall = new Firewall();
});

it('returns false for normal browser', function (): void {
    $request = request();
    request()->server->set('HTTP_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    request()->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

    expect($this->firewall->isBot($request))->toBeFalse();
});

it('detects bots via standard UA parser', function (): void {
    $request = request();
    request()->server->set('HTTP_USER_AGENT', 'Googlebot/2.1 (+http://www.google.com/bot.html)');
    request()->headers->set('User-Agent', 'Googlebot/2.1 (+http://www.google.com/bot.html)');

    expect($this->firewall->isBot($request))->toBeTrue();
});

it('detects empty user agent as bot', function (): void {
    $request = request();
    request()->server->set('HTTP_USER_AGENT', '');
    request()->headers->set('User-Agent', '');

    expect($this->firewall->isBot($request))->toBeTrue();
});

it('detects GPTBot AI crawler', function (): void {
    $request = request();
    request()->server->set('HTTP_USER_AGENT', 'Mozilla/5.0 (compatible; GPTBot/1.0)');
    request()->headers->set('User-Agent', 'Mozilla/5.0 (compatible; GPTBot/1.0)');

    expect($this->firewall->isBot($request))->toBeTrue();
});

it('detects ClaudeBot AI crawler', function (): void {
    $request = request();
    request()->server->set('HTTP_USER_AGENT', 'ClaudeBot/1.0 (+https://claude.ai/bot)');
    request()->headers->set('User-Agent', 'ClaudeBot/1.0 (+https://claude.ai/bot)');

    expect($this->firewall->isBot($request))->toBeTrue();
});

it('detects PerplexityBot AI crawler', function (): void {
    $request = request();
    request()->server->set('HTTP_USER_AGENT', 'PerplexityBot/1.0');
    request()->headers->set('User-Agent', 'PerplexityBot/1.0');

    expect($this->firewall->isBot($request))->toBeTrue();
});

it('detects Google-Extended AI crawler', function (): void {
    $request = request();
    request()->server->set('HTTP_USER_AGENT', 'Mozilla/5.0 (compatible; Google-Extended)');
    request()->headers->set('User-Agent', 'Mozilla/5.0 (compatible; Google-Extended)');

    expect($this->firewall->isBot($request))->toBeTrue();
});

it('detects HeadlessChrome automation', function (): void {
    $request = request();
    request()->server->set('HTTP_USER_AGENT', 'Mozilla/5.0 HeadlessChrome/120.0.0.0 Safari/537.36');
    request()->headers->set('User-Agent', 'Mozilla/5.0 HeadlessChrome/120.0.0.0 Safari/537.36');

    expect($this->firewall->isBot($request))->toBeTrue();
});

it('allows Googlebot (search indexing) via isBlockedCrawler', function (): void {
    $request = request();
    request()->server->set('HTTP_USER_AGENT', 'Googlebot/2.1 (+http://www.google.com/bot.html)');
    request()->headers->set('User-Agent', 'Googlebot/2.1 (+http://www.google.com/bot.html)');

    expect($this->firewall->isBlockedCrawler($request))->toBeFalse();
});

it('blocks GPTBot via isBlockedCrawler', function (): void {
    $request = request();
    request()->server->set('HTTP_USER_AGENT', 'Mozilla/5.0 (compatible; GPTBot/1.0)');
    request()->headers->set('User-Agent', 'Mozilla/5.0 (compatible; GPTBot/1.0)');

    expect($this->firewall->isBlockedCrawler($request))->toBeTrue();
});
