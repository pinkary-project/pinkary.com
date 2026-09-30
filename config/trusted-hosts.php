<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Hosts
    |--------------------------------------------------------------------------
    |
    | AbsoluteUrl rewrites stored, APP_URL-rooted media URLs onto the host
    | the request arrived on, so that a device reaching the API by another
    | name still receives images it can actually load. That makes the result
    | depend on the Host header -- and, because the application trusts every
    | proxy, on X-Forwarded-Host as well.
    |
    | Rewriting is therefore performed only for the hosts listed here, so a
    | forged header cannot repoint every avatar and image in a response at
    | an origin of the caller's choosing. APP_URL's own host and the local
    | development names are always allowed and need not be repeated.
    |
    | Comma separated, e.g. "pinkary.test,192.168.1.10". Only needed when
    | the API is reached by a name other than APP_URL's -- a phone on the
    | local network, or a separate API hostname whose storage is not
    | publicly reachable under APP_URL.
    |
    */

    'hosts' => array_values(array_filter(array_map(
        trim(...),
        explode(',', (string) env('APP_TRUSTED_HOSTS', '')),
    ))),

];
