<?php

declare(strict_types=1);

use App\Services\Firewall;
use Illuminate\Http\Request;

beforeEach(function (): void {
    $this->firewall = new Firewall();
});

function userAgentRequest(string $userAgent): Request
{
    $request = request();
    $request->headers->set('User-Agent', $userAgent);

    return $request;
}

it('returns false for normal browser', function (): void {
    expect($this->firewall->isBot(userAgentRequest('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')))->toBeFalse();
});

it('detects bots via standard UA parser', function (): void {
    expect($this->firewall->isBot(userAgentRequest('Googlebot/2.1 (+http://www.google.com/bot.html)')))->toBeTrue();
});

it('detects empty user agent as bot', function (): void {
    expect($this->firewall->isBot(userAgentRequest('')))->toBeTrue();
});

it('detects GPTBot AI crawler', function (): void {
    expect($this->firewall->isBot(userAgentRequest('Mozilla/5.0 (compatible; GPTBot/1.0)')))->toBeTrue();
});

it('detects ClaudeBot AI crawler', function (): void {
    expect($this->firewall->isBot(userAgentRequest('ClaudeBot/1.0 (+https://claude.ai/bot)')))->toBeTrue();
});

it('detects PerplexityBot AI crawler', function (): void {
    expect($this->firewall->isBot(userAgentRequest('PerplexityBot/1.0')))->toBeTrue();
});

it('detects Google-Extended AI crawler', function (): void {
    expect($this->firewall->isBot(userAgentRequest('Mozilla/5.0 (compatible; Google-Extended)')))->toBeTrue();
});

it('detects HeadlessChrome automation', function (): void {
    expect($this->firewall->isBot(userAgentRequest('Mozilla/5.0 HeadlessChrome/120.0.0.0 Safari/537.36')))->toBeTrue();
});

it('matches user agents case-insensitively', function (): void {
    expect($this->firewall->isBlockedCrawler(userAgentRequest('mozilla/5.0 (compatible; gptbot/1.0)')))->toBeTrue()
        ->and($this->firewall->isBlockedCrawler(userAgentRequest('Mozilla/5.0 headlesschrome/120.0.0.0 Safari/537.36')))->toBeTrue();
});

it('allows Googlebot (search indexing) via isBlockedCrawler', function (): void {
    expect($this->firewall->isBlockedCrawler(userAgentRequest('Googlebot/2.1 (+http://www.google.com/bot.html)')))->toBeFalse();
});

it('blocks GPTBot via isBlockedCrawler', function (): void {
    expect($this->firewall->isBlockedCrawler(userAgentRequest('Mozilla/5.0 (compatible; GPTBot/1.0)')))->toBeTrue();
});
