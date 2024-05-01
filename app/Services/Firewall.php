<?php

declare(strict_types=1);

namespace App\Services;

use DeviceDetector\Parser\Bot as BotParser;
use Illuminate\Http\Request;

final readonly class Firewall
{
    /**
     * Determine if the request is from a bot.
     */
    public function isBot(Request $request): bool
    {
        $userAgent = (string) $request->userAgent();

        $botParser = new BotParser();
        $botParser->setUserAgent($userAgent);
        $botParser->discardDetails();

        return ! is_null($botParser->parse());
    }
}
