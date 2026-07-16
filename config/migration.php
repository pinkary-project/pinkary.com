<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Migration Target Database
    |--------------------------------------------------------------------------
    |
    | Connection settings for the target MySQL database used by the
    | migrate:sqlite-to-mysql command. Set these TARGET_DB_* values
    | in your .env on the Forge server before running the migration.
    |
    */

    'target_db' => [
        'url' => env('TARGET_DB_URL'),
        'host' => env('TARGET_DB_HOST'),
        'port' => env('TARGET_DB_PORT', '3306'),
        'database' => env('TARGET_DB_DATABASE'),
        'username' => env('TARGET_DB_USERNAME'),
        'password' => env('TARGET_DB_PASSWORD'),
    ],

];
