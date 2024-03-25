<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | The list of sponsors usernames
    |--------------------------------------------------------------------------
    |
    | Here you may define the list of sponsors usernames that are "fixed" and
    | should all be considered always as sponsors regardless of the user's
    | sponsorship status. The user usernames should be comma-separated.
    |
    */

    'github_usernames' => collect(explode(',', (string) env('SPONSORS_GITHUB_USERNAMES', '')))->map( // @phpstan-ignore-line
        fn (string $username): string => trim($username)
    )->filter()->values()->all(),
];
