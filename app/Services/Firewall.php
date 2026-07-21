<?php

declare(strict_types=1);

namespace App\Services;

use DeviceDetector\Parser\Bot as BotParser;
use Illuminate\Http\Request;

final readonly class Firewall
{
    private const array AI_CRAWLERS = [
        'GPTBot',
        'ChatGPT-User',
        'Claude-Web',
        'ClaudeBot',
        'anthropic-ai',
        'Google-Extended',
        'CCBot',
        'PerplexityBot',
        'Perplexity-User',
        'cohere-ai',
        'OAI-SearchBot',
        'Applebot-Extended',
        'Bytespider',
        'ImagesiftBot',
        'Meta-ExternalAgent',
        'AI2Bot',
        'Diffbot',
        'Kangaroo Bot',
        'Seekr',
        'Timpibot',
        'VelenPublicWebCrawler',
        'Webzio-Extended',
        'YouBot',
    ];

    private const array HEADLESS_BROWSERS = [
        'HeadlessChrome',
        'HeadlessChromium',
    ];

    /**
     * Determine if the request is from any bot (AI crawler, automation
     * tool, or standard search-engine crawler).
     *
     * This is the broad check — used wherever we want to detect all
     * non-human traffic (e.g. view-count filtering).
     */
    public function isBot(Request $request): bool
    {
        $userAgent = (string) $request->userAgent();

        if ($userAgent === '') {
            return true;
        }

        if ($this->matchesPattern($userAgent)) {
            return true;
        }

        $botParser = new BotParser();
        $botParser->setUserAgent($userAgent);
        $botParser->discardDetails();

        return ! is_null($botParser->parse());
    }

    /**
     * Determine if the request should be blocked from serving content.
     *
     * Narrower than isBot() — targets only AI-training crawlers and
     * headless automation tools. Legitimate search-engine bots such
     * as Googlebot are allowed through so they can index the site.
     *
     * This does NOT address authenticated spam, fake-account
     * registration, or abuse from logged-in users — those require
     * separate rate-limiting, CAPTCHA, and fraud-detection systems.
     */
    public function isBlockedCrawler(Request $request): bool
    {
        $userAgent = (string) $request->userAgent();

        if ($userAgent === '') {
            return true;
        }

        return $this->matchesPattern($userAgent);
    }

    private function matchesPattern(string $userAgent): bool
    {
        foreach (self::AI_CRAWLERS as $crawler) {
            if (str_contains($userAgent, $crawler)) {
                return true;
            }
        }

        foreach (self::HEADLESS_BROWSERS as $browser) {
            if (str_contains($userAgent, $browser)) {
                return true;
            }
        }

        return false;
    }
}
