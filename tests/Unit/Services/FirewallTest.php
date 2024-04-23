<?php

declare(strict_types=1);

use App\Services\Firewall;

it('detects bots', function () {
    $firewall = new Firewall();

    $request = request();

    expect($firewall->isBot($request))->toBeFalse();

    request()->server->set('HTTP_USER_AGENT', 'Googlebot/2.1 (+http://www.google.com/bot.html)');
    request()->headers->set('User-Agent', 'Googlebot/2.1 (+http://www.google.com/bot.html)');

    expect($firewall->isBot($request))->toBeTrue();
});
