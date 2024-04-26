<?php

declare(strict_types=1);

namespace App\Services;

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Illuminate\Http\Request;

final readonly class Firewall
{
    /**
     * Determine if the request is from a bot.
     */
    public function isBot(Request $request): bool
    {
        return $this->device($request)->isBot();
    }

    /**
     * Get the device detector instance.
     */
    private function device(Request $request): DeviceDetector
    {
        $userAgent = (string) $request->userAgent();
        $clientHints = ClientHints::factory($request->server->all());

        AbstractDeviceParser::setVersionTruncation(AbstractDeviceParser::VERSION_TRUNCATION_NONE);

        $device = new DeviceDetector($userAgent, $clientHints);

        $device->parse();

        return $device;
    }
}
